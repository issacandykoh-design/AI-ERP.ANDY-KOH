<?php

namespace App\Http\Controllers;

use App\DataTables\ExpensesDataTable;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\Admin\Employee\ImportProcessRequest;
use App\Http\Requests\Admin\Employee\ImportRequest;
use App\Http\Requests\Expenses\StoreExpense;
use App\Imports\ExpenseImport;
use App\Jobs\ImportExpenseJob;
use App\Models\BankAccount;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\ExpensesCategory;
use App\Models\ExpensesCategoryRole;
use App\Models\Project;
use App\Models\User;
use App\Scopes\ActiveScope;
use App\Traits\ImportExcel;
use Illuminate\Http\Request;

class ExpenseController extends AccountBaseController
{
    use ImportExcel;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.expenses';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('expenses', $this->user->modules));
            return $next($request);
        });
    }

    public function index(ExpensesDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_expenses');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        if (!request()->ajax()) {
            $this->employees = User::allEmployees(null, true);
            $this->projects = Project::allProjects();
            $this->categories = ExpenseCategoryController::getCategoryByCurrentRole();
        }

        return $dataTable->render('expenses.index', $this->data);

    }

    public function changeStatus(Request $request)
    {
        abort_403(user()->permission('approve_expenses') != 'all');

        $expenseId = $request->expenseId;
        $status = $request->status;
        $expense = Expense::findOrFail($expenseId);
        $expense->status = $status;
        $expense->save();
        return Reply::success(__('messages.updateSuccess'));
    }

    public function show($id)
    {
        $this->expense = Expense::with(['user', 'project', 'category', 'transactions' => function($q){
            $q->orderByDesc('id')->limit(1);
        }, 'transactions.bankAccount'])->findOrFail($id)->withCustomFields();

        $this->viewPermission = user()->permission('view_expenses');
        $viewProjectPermission = user()->permission('view_project_expenses');
        $this->editExpensePermission = user()->permission('edit_expenses');
        $this->deleteExpensePermission = user()->permission('delete_expenses');

        abort_403(!($this->viewPermission == 'all'
        || ($this->viewPermission == 'added' && $this->expense->added_by == user()->id)
        || ($viewProjectPermission == 'owned' || $this->expense->user_id == user()->id)));

        $getCustomFieldGroupsWithFields = $this->expense->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->pageTitle = $this->expense->item_name;
        $this->view = 'expenses.ajax.show';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('expenses.show', $this->data);

    }

    public function create()
    {
        $this->addPermission = user()->permission('add_expenses');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->currencies = Currency::all();
        $this->categories = ExpenseCategoryController::getCategoryByCurrentRole();
        $this->linkExpensePermission = user()->permission('link_expense_bank_account');
        $this->viewBankAccountPermission = user()->permission('view_bankaccount');

        $bankAccounts = BankAccount::where('status', 1)->where('currency_id', company()->currency_id);

        if($this->viewBankAccountPermission == 'added'){
            $bankAccounts = $bankAccounts->where('added_by', user()->id);
        }

        $bankAccounts = $bankAccounts->get();
        $this->bankDetails = $bankAccounts;
        
        // 修复companyCurrency为null的问题
        if (company() && company()->currency_id) {
            $this->companyCurrency = Currency::where('id', company()->currency_id)->first();
        }
        
        // 如果仍然为null，使用默认货币或创建一个默认的USD货币
        if (!$this->companyCurrency) {
            $this->companyCurrency = Currency::where('currency_code', 'USD')->first();
            
            // 如果连USD都没有，创建一个默认的货币对象
            if (!$this->companyCurrency) {
                $this->companyCurrency = new Currency();
                $this->companyCurrency->id = 0;
                $this->companyCurrency->currency_code = 'USD';
                $this->companyCurrency->currency_symbol = '$';
                $this->companyCurrency->currency_name = 'US Dollar';
            }
        }

        // Get only current login employee projects
        if ($this->addPermission == 'added') {
            $this->projects = Project::where('added_by', user()->id)->orWhereHas('projectMembers', function ($query) {
                $query->where('user_id', user()->id);
            })->get();

        } else {
            $this->projects = Project::all();
        }

        $this->pageTitle = __('modules.expenses.addExpense');
        $this->projectId = request('project_id') ? request('project_id') : null;

        if (!is_null($this->projectId)) {
            $this->project = Project::with('projectMembers')->where('id', $this->projectId)->first();
            $this->projectName = $this->project->project_name;
            $this->employees = $this->project->projectMembers;

        } else {
            $this->employees = User::allEmployees(null, true);
        }

        $expense = new Expense();

        $getCustomFieldGroupsWithFields = $expense->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->view = 'expenses.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('expenses.show', $this->data);

    }

    public function store(StoreExpense $request)
    {
        $currencySetting = Currency::find($request->currency_id);
        
        // 如果找不到指定的货币，使用公司默认货币或USD
        if (!$currencySetting) {
            $currencySetting = Currency::find(company()->currency_id);
            
            // 如果公司默认货币也不存在，使用USD
            if (!$currencySetting) {
                $currencySetting = Currency::where('currency_code', 'USD')->first();
            }
            
            // 如果连USD都没有，创建一个默认的货币设置
            if (!$currencySetting) {
                $currencySetting = new Currency();
                $currencySetting->id = 0;
                $currencySetting->currency_code = 'USD';
                $currencySetting->currency_name = 'US Dollar';
                $currencySetting->currency_symbol = '$';
                $currencySetting->no_of_decimal = 2;
                $currencySetting->exchange_rate = 1;
            }
            
            // 更新请求中的currency_id为找到的有效货币ID
            $request->merge(['currency_id' => $currencySetting->id]);
        }

        $userRole = session('user_roles');
        $expense = new Expense();
        $expense->item_name = $request->item_name;
        $expense->purchase_date = companyToYmd($request->purchase_date);
        $expense->purchase_from = $request->purchase_from;
        $expense->price = round($request->price, $currencySetting->no_of_decimal);
        $expense->currency_id = $request->currency_id;
        $expense->category_id = $request->category_id;
        $expense->user_id = $request->user_id;
        $expense->company_id = company()->id;
        $expense->default_currency_id = company()->currency_id;
        $expense->exchange_rate = $request->exchange_rate;
        $expense->description = trim_editor($request->description);

        if ($userRole[0] == 'admin') {
            $expense->status = 'approved';
            $expense->approver_id = user()->id;
        }

        if ($request->has('status')) {
            $expense->status = $request->status;
            $expense->approver_id = user()->id;
        }

        if ($request->has('project_id') && $request->project_id != '0') {
            $expense->project_id = $request->project_id;
        }

        if ($request->hasFile('bill')) {
            $filename = Files::uploadLocalOrS3($request->bill, Expense::FILE_PATH);
            $expense->bill = $filename;
        }

        $expense->bank_account_id = $request->bank_account_id;

        $expense->save();

        // To add custom fields data
        if ($request->custom_fields_data) {
            $expense->updateCustomFieldData($request->custom_fields_data);
        }

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('expenses.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function edit($id)
    {
        $this->expense = Expense::findOrFail($id)->withCustomFields();
        $this->editPermission = user()->permission('edit_expenses');

        abort_403(!($this->editPermission == 'all' || ($this->editPermission == 'added' && $this->expense->added_by == user()->id)));

        $this->currencies = Currency::all();
        $this->categories = ExpenseCategoryController::getCategoryByCurrentRole();
        $this->employees = User::allEmployees(null, false);
        $this->pageTitle = __('modules.expenses.updateExpense');
        $this->linkExpensePermission = user()->permission('link_expense_bank_account');
        $this->viewBankAccountPermission = user()->permission('view_bankaccount');

        $bankAccounts = BankAccount::where('status', 1)->where('currency_id', $this->expense->currency_id);

        if($this->viewBankAccountPermission == 'added'){
            $bankAccounts = $bankAccounts->where('added_by', user()->id);
        }

        $bankAccounts = $bankAccounts->get();
        $this->bankDetails = $bankAccounts;


        $userId = $this->expense->user_id;

        if (!is_null($userId)) {
            $this->projects = Project::with('members')->whereHas('members', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->get();
        }
        else {
            $this->projects = Project::get();
        }

        $this->companyCurrency = Currency::where('id', company()->currency_id)->first();

        $expense = new Expense();

        $getCustomFieldGroupsWithFields = $expense->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->view = 'expenses.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('expenses.show', $this->data);

    }

    public function update(StoreExpense $request, $id)
    {
        $currencySetting = Currency::find($request->currency_id);
        
        // 如果找不到指定的货币，使用公司默认货币或USD
        if (!$currencySetting) {
            $currencySetting = Currency::find(company()->currency_id);
            
            // 如果公司默认货币也不存在，使用USD
            if (!$currencySetting) {
                $currencySetting = Currency::where('currency_code', 'USD')->first();
            }
            
            // 如果连USD都没有，创建一个默认的货币设置
            if (!$currencySetting) {
                $currencySetting = new Currency();
                $currencySetting->id = 0;
                $currencySetting->currency_code = 'USD';
                $currencySetting->currency_name = 'US Dollar';
                $currencySetting->currency_symbol = '$';
                $currencySetting->no_of_decimal = 2;
                $currencySetting->exchange_rate = 1;
            }
            
            // 更新请求中的currency_id为找到的有效货币ID
            $request->merge(['currency_id' => $currencySetting->id]);
        }

        $expense = Expense::findOrFail($id);
        $expense->item_name = $request->item_name;
        $expense->purchase_date = companyToYmd($request->purchase_date);
        $expense->purchase_from = $request->purchase_from;
        $expense->price = round($request->price, $currencySetting->no_of_decimal);
        $expense->currency_id = $request->currency_id;
        $expense->user_id = $request->user_id;
        $expense->category_id = $request->category_id;
        $expense->default_currency_id = company()->currency_id;
        $expense->exchange_rate = $request->exchange_rate;
        $expense->description = trim_editor($request->description);

        $expense->project_id = ($request->project_id > 0) ? $request->project_id : null;


        if ($request->bill_delete == 'yes') {
            Files::deleteFile($expense->bill, Expense::FILE_PATH);
            $expense->bill = null;
        }

        if ($request->hasFile('bill')) {
            Files::deleteFile($expense->bill, Expense::FILE_PATH);

            $filename = Files::uploadLocalOrS3($request->bill, Expense::FILE_PATH);
            $expense->bill = $filename;
        }

        if ($request->has('status')) {
            $expense->status = $request->status;
        }

        $expense->bank_account_id = $request->bank_account_id;
        $expense->save();

        // To add custom fields data
        if ($request->custom_fields_data) {
            $expense->updateCustomFieldData($request->custom_fields_data);
        }

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('expenses.index')]);

    }

    public function destroy($id)
    {
        $this->expense = Expense::findOrFail($id);
        $this->deletePermission = user()->permission('delete_expenses');
        abort_403(!($this->deletePermission == 'all' || ($this->deletePermission == 'added' && $this->expense->added_by == user()->id)));

        Expense::destroy($id);
        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);
                return Reply::success(__('messages.deleteSuccess'));
        case 'change-status':
            $this->changeBulkStatus($request);
                return Reply::success(__('messages.updateSuccess'));
        default:
                return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_employees') != 'all');

        // Did this to call observer
        foreach (Expense::withoutGlobalScope(ActiveScope::class)->whereIn('id', explode(',', $request->row_ids))->get() as $delete) {
            $delete->delete();
        }
    }

    protected function changeBulkStatus($request)
    {
        abort_403(user()->permission('edit_employees') != 'all');

        $expenses = Expense::withoutGlobalScope(ActiveScope::class)->whereIn('id', explode(',', $request->row_ids))->get();

        $expenses->each(function ($expense) use ($request) {
            $expense->status = $request->status;
            $expense->save();
        });
    }

    protected function getEmployeeProjects(Request $request)
    {
        // Get employee category
        if (!is_null($request->userId)) {
            $categories = ExpensesCategory::with('roles')->whereHas('roles', function($q) use ($request) {
                $user = User::withoutGlobalScope(ActiveScope::class)->findOrFail($request->userId);

                $roleId = (count($user->role) > 1) ? $user->role[1]->role_id : $user->role[0]->role_id;
                $q->where('role_id', $roleId);
            })->get();

        }
        else {
            $categories = ExpensesCategory::get();
        }

        if($categories) {
            foreach ($categories as $category) {
                $selected = $category->id == $request->categoryId ? 'selected' : '';
                $categories .= '<option value="' . $category->id . '"'.$selected.'>' . $category->category_name . '</option>';
            }
        }

        // Get employee project
        if (!is_null($request->userId)) {
            $projects = Project::with('members')->whereHas('members', function ($q) use ($request) {
                $q->where('user_id', $request->userId);
            })->get();
        }
        else if(user()->permission('add_expenses') == 'all' && is_null($request->userId))
        {
            $projects = [];
        }
        else {
            $projects = Project::get();
        }

        $data = null;

        if ($projects) {
            foreach ($projects as $project) {
                $data .= '<option data-currency-id="'. $project->currency_id .'" value="' . $project->id . '">' . $project->project_name . '</option>';
            }
        }


        return Reply::dataOnly(['status' => 'success', 'data' => $data, 'category' => $categories]);
    }

    protected function getCategoryEmployee(Request $request)
    {
        $expenseCategory = ExpensesCategoryRole::where('expenses_category_id', $request->categoryId)->get();
        $roleId = [];
        $managers = [];
        $employees = [];

        foreach($expenseCategory as $category) {
            array_push($roleId, $category->role_id);
        }

        if (count($roleId ) == 1 && $roleId != null) {
            $users = User::whereHas(
                'role', function($q)  use ($roleId) {
                    $q->whereIn('role_id', $roleId);
                }
            )->get();

            foreach ($users as $user) {
                ($user->hasRole('Manager')) ? array_push($managers, $user) : array_push($employees, $user);
            }
        }
        else {
            $employees = User::allEmployees(null, false);
        }

        $data = null;

        if ($employees) {
            foreach ($employees as $employee) {
                if($employee->status == 'active' || $employee->id == $request->userId){
                    $data .= '<option ';

                    $content = ($employee->status == 'deactive') ? "<span class='badge badge-pill badge-danger border align-center ml-2 px-2'>Inactive</span>" : '';
                    $selected = $employee->id == $request->userId ? 'selected' : '';
                    $itsYou = $employee->id == user()->id ? "<span class='ml-2 badge badge-secondary pr-1'>". __('app.itsYou') .'</span>' : '';

                    $data .= 'data-content="<div class=\'d-inline-block mr-1\'><img class=\'taskEmployeeImg rounded-circle\' src=\'' . $employee->image_url . '\' ></div> '.$employee->name.$itsYou.$content.'"
                    value="' . $employee->id . '"'.$selected.'>'.$employee->name.'</option>';
                }
            }
        }
        else {
            foreach ($managers as $manager) {
                $data .= '<option ';

                $selected = $manager->id == $request->userId ? 'selected' : '';
                $itsYou = $manager->id == user()->id ? "<span class='ml-2 badge badge-secondary pr-1'>" . __('app.itsYou') . '</span>' : '';
                $data .= 'data-content="<div class=\'d-inline-block mr-1\'><img class=\'taskEmployeeImg rounded-circle\' src=\'' . $manager->image_url . '\' ></div> '.$manager->name.'"
                value="' . $manager->id . '"'.$selected.'>'.$manager->name.$itsYou.'</option>';
            }
        }

        return Reply::dataOnly(['status' => 'success', 'employees' => $data]);
    }

    public function import()
    {
        $this->pageTitle = __('app.importExcel') . ' ' . __('app.menu.expenses');

        $addPermission = user()->permission('add_expenses');
        abort_403(!in_array($addPermission, ['all', 'added']));

        $this->view = 'expenses.ajax.import';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('expenses.show', $this->data);
    }

    public function importStore(ImportRequest $request)
    {
        $rvalue = $this->importFileProcess($request, ExpenseImport::class);

        if($rvalue == 'abort'){
            return Reply::error(__('messages.abortAction'));
        }

        $view = view('expenses.ajax.import_progress', $this->data)->render();

        return Reply::successWithData(__('messages.importUploadSuccess'), ['view' => $view]);
    }

    public function importProcess(ImportProcessRequest $request)
    {
        $batch = $this->importJobProcess($request, ExpenseImport::class, ImportExpenseJob::class);

        return Reply::successWithData(__('messages.importProcessStart'), ['batch' => $batch]);
    }

    public function ocr(Request $request)
    {
        // Check permission
        $addPermission = user()->permission('add_expenses');
        abort_403(!in_array($addPermission, ['all', 'added']));

        try {
            // Validate the request
            $request->validate([
                'image' => 'required|image|mimes:jpeg,jpg,png,gif,bmp,tiff|max:10240', // 10MB max
                'language' => 'required|string|in:eng,chi_sim,chi_tra,fra,deu,spa,jpn,kor'
            ]);

            $image = $request->file('image');
            $language = $request->input('language', 'eng');

            // Create a temporary file path
            $tempPath = storage_path('app/temp/ocr/');
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }

            $fileName = uniqid() . '.' . $image->getClientOriginalExtension();
            $fullPath = $tempPath . $fileName;

            // Move the uploaded file to temp directory
            $image->move($tempPath, $fileName);

            // Perform OCR using Tesseract
            $ocrText = $this->performOCR($fullPath, $language);

            // Clean up the temporary file
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            if ($ocrText === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'OCR processing failed. Please ensure Tesseract is installed and configured properly.'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'text' => $ocrText,
                'message' => 'OCR processing completed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    private function performOCR($imagePath, $language = 'eng')
    {
        try {
            // Check if Tesseract is available
            $tesseractPath = $this->getTesseractPath();

            if (!$tesseractPath) {
                // Fallback to online OCR service or return demo text
                return $this->fallbackOCR($imagePath);
            }

            // Escape the file path for shell command
            $escapedPath = escapeshellarg($imagePath);
            $escapedLanguage = escapeshellarg($language);

            // Build the Tesseract command
            $command = "\"{$tesseractPath}\" {$escapedPath} stdout -l {$escapedLanguage}";

            // Execute the command
            $output = shell_exec($command);

            if ($output === null) {
                return false;
            }

            // Clean up the output
            $cleanText = trim($output);

            // If no text was extracted, return a message
            if (empty($cleanText)) {
                return "No text could be extracted from the image. Please ensure the image contains clear, readable text.";
            }

            return $cleanText;

        } catch (\Exception $e) {
            return false;
        }
    }

    private function getTesseractPath()
    {
        // Common Tesseract installation paths
        $possiblePaths = [
            'tesseract', // If in PATH
            '/usr/bin/tesseract', // Linux
            '/usr/local/bin/tesseract', // macOS with Homebrew
            'C:\\Program Files\\Tesseract-OCR\\tesseract.exe', // Windows
            'C:\\Program Files (x86)\\Tesseract-OCR\\tesseract.exe', // Windows 32-bit
        ];

        foreach ($possiblePaths as $path) {
            if ($this->commandExists($path)) {
                return $path;
            }
        }

        return null;
    }

    private function commandExists($command)
    {
        // For Windows, check if the file exists directly
        if (PHP_OS_FAMILY === 'Windows') {
            // If it's a full path, check if file exists
            if (strpos($command, ':') !== false || strpos($command, '\\') !== false) {
                return file_exists($command) && is_executable($command);
            }
        }

        $whereIsCommand = (PHP_OS_FAMILY === 'Windows') ? 'where' : 'which';

        $process = proc_open(
            "$whereIsCommand $command",
            [
                0 => ['pipe', 'r'], // stdin
                1 => ['pipe', 'w'], // stdout
                2 => ['pipe', 'w'], // stderr
            ],
            $pipes
        );

        if ($process !== false) {
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $returnCode = proc_close($process);

            return $returnCode === 0;
        }

        return false;
    }

    private function fallbackOCR($imagePath)
    {
        // This is a fallback method when Tesseract is not available
        // In a real implementation, you might want to use an online OCR service
        // For demo purposes, we'll return a sample text

        return "OCR Demo Mode: Tesseract not found on this system.\n\n" .
               "This is a demonstration of the OCR functionality. " .
               "To enable real OCR processing, please install Tesseract OCR on your server.\n\n" .
               "Installation instructions:\n" .
               "- Windows: Download from https://github.com/UB-Mannheim/tesseract/wiki\n" .
               "- Linux: sudo apt-get install tesseract-ocr\n" .
               "- macOS: brew install tesseract\n\n" .
               "Image file: " . basename($imagePath) . "\n" .
               "File size: " . (file_exists($imagePath) ? number_format(filesize($imagePath) / 1024, 2) . " KB" : "Unknown");
    }

}
