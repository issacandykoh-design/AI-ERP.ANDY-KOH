(function(){
  'use strict';

  function isMobile(){
    var ua = navigator.userAgent || navigator.vendor || window.opera;
    var byUA = /android|iphone|ipad|ipod|mobile/i.test(ua);
    var byViewport = Math.min(window.innerWidth, window.innerHeight) <= 820;
    return byUA || byViewport;
  }

  if(!isMobile()) return;

  function ready(fn){
    if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
    else fn();
  }

  function findSidebar(){
    var container = document.querySelector('aside, [role="complementary"], [class*="sidebar"], [class*="Side"], [class*="side"]');
    if(container && container.id === 'root') container = null;
    if(!container){
      // fallback by text markers but avoid picking #root/body
      var walker = document.createTreeWalker(document.body, NodeFilter.SHOW_ELEMENT, null);
      while(walker.nextNode()){
        var el = walker.currentNode;
        if(el === document.body) continue;
        if(el.id === 'root') continue;
        var text = (el.textContent || '').toLowerCase();
        if(text.includes('new chat') || text.includes('no conversations')){
          container = el.closest('aside, [role="complementary"], [class*="sidebar"], [class*="Side"], [class*="side"]');
          if(container) break;
        }
      }
    }
    return container || null;
  }

  function createHistoryUI(sidebar){
    var btn = document.createElement('button');
    btn.className = 'crv-history-btn';
    btn.setAttribute('aria-label','Conversation History');
    btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" stroke="#5f4a3c" stroke-width="1.6"/><path d="M12 7v5l3 3" stroke="#5f4a3c" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';

    var backdrop = document.createElement('div');
    backdrop.className = 'crv-modal-backdrop';
    backdrop.style.display = 'none';

    var modal = document.createElement('div');
    modal.className = 'crv-modal';
    modal.style.display = 'none';

    var header = document.createElement('div');
    header.className = 'crv-modal-header';
    var title = document.createElement('div');
    title.className = 'crv-modal-title';
    title.textContent = 'Conversation History';
    var close = document.createElement('button');
    close.className = 'crv-modal-close';
    close.textContent = 'Close';
    header.appendChild(title); header.appendChild(close);

    var body = document.createElement('div');
    body.className = 'crv-modal-body';
    var list = document.createElement('ul');
    list.className = 'crv-modal-list';
    body.appendChild(list);
    modal.appendChild(header); modal.appendChild(body);

    document.body.appendChild(btn);
    document.body.appendChild(backdrop);
    document.body.appendChild(modal);

    function extractItems(){
      var items = [];
      var candidates = sidebar.querySelectorAll('a, button, li, [role="listitem"], [class*="conversation"]');
      candidates.forEach(function(el){
        var txt = (el.textContent || '').trim();
        if(!txt) return;
        // heuristic: conversation entries usually short title + time
        var title = txt.split('\n')[0].trim();
        var timeNode = el.querySelector('time, [datetime]');
        var timeTxt = timeNode ? (timeNode.getAttribute('datetime') || timeNode.textContent || '').trim() : '';
        items.push({ el: el, title: title, time: timeTxt });
      });
      return items;
    }

    function renderList(){
      list.innerHTML = '';
      var items = extractItems();
      items.forEach(function(it){
        var li = document.createElement('li');
        li.className = 'crv-modal-item';
        var t = document.createElement('div'); t.className = 'crv-item-title'; t.textContent = it.title || '未命名对话';
        var tm = document.createElement('div'); tm.className = 'crv-item-time'; tm.textContent = it.time || '';
        li.appendChild(t); li.appendChild(tm);
        li.addEventListener('click', function(){
          try { it.el.click(); } catch(e){}
          closeModal();
        });
        list.appendChild(li);
      });
    }

    function open(){
      backdrop.style.display = 'block';
      modal.style.display = 'flex';
      renderList();
    }
    function closeModal(){
      backdrop.style.display = 'none';
      modal.style.display = 'none';
    }

    btn.addEventListener('click', open);
    backdrop.addEventListener('click', closeModal);
    close.addEventListener('click', closeModal);

    // hide sidebar by default on mobile (ensure not hiding root)
    if(sidebar && sidebar.id !== 'root') sidebar.classList.add('crv-mobile-hidden');
  }

  // ---- Fixed second row (PPT & Excel) ----
  function clickOriginalModeButton(mode){
    var text = mode.toLowerCase();
    var candidates = document.querySelectorAll('.sub-buttons-container .sub-button, button, a');
    for(var i=0;i<candidates.length;i++){
      var el = candidates[i];
      var t = (el.textContent || '').toLowerCase();
      if(t.includes(text)){
        try { el.click(); } catch(e){}
        return true;
      }
    }
    try { localStorage.setItem('selectedOption', mode); } catch(e){}
    return false;
  }

  function ensureSecondRowButtons(){
    // 移除旧的固定容器（如果存在）
    var fixed = document.querySelector('.crv-second-row');
    if(fixed) try { fixed.remove(); } catch(e){}

    // 把 Excel / PPT 紧贴地放到顶端按钮容器的下面（同一容器中）
    var holder = document.querySelector('.sub-buttons-container');
    if(!holder) return;

    // 查找页面中的 Excel/PPT/Graphics 按钮（优先选择 holder 内部，如果不在则全局查找并移入 holder）
    var excelBtn = null, pptBtn = null, gfxBtn = null;
    var candidates = document.querySelectorAll('button, a, .sub-buttons-container .sub-button');
    for(var i=0;i<candidates.length;i++){
      var el = candidates[i];
      var txt = (el.textContent || '').trim().toLowerCase();
      if(!excelBtn && txt.includes('excel')) excelBtn = el;
      else if(!pptBtn && txt.includes('ppt')) pptBtn = el;
      else if(!gfxBtn && (txt.includes('graphics') || txt.includes('图形'))) gfxBtn = el;
      if(excelBtn && pptBtn && gfxBtn) break;
    }

    function moveIntoHolder(btn, cls){
      if(!btn) return false;
      try {
        // 如果按钮不在 holder 内，则移入
        if(!holder.contains(btn)) holder.appendChild(btn);
        btn.classList.add(cls); // 标记以应用第二行样式
        return true;
      } catch(e){ return false; }
    }

    var hasExcel = moveIntoHolder(excelBtn, 'crv-excel-btn');
    var hasPpt = moveIntoHolder(pptBtn, 'crv-ppt-btn');
    var hasGfx = moveIntoHolder(gfxBtn, 'crv-gfx-btn');

    // 若未找到某个按钮，生成一个替代按钮并触发原逻辑
    if(!hasExcel){
      var excel = document.createElement('button');
      excel.textContent = 'Excel';
      excel.className = 'sub-button crv-excel-btn';
      excel.addEventListener('click', function(){ clickOriginalModeButton('Excel'); });
      holder.appendChild(excel);
    }
    if(!hasPpt){
      var ppt = document.createElement('button');
      ppt.textContent = 'PPT';
      ppt.className = 'sub-button crv-ppt-btn';
      ppt.addEventListener('click', function(){ clickOriginalModeButton('PPT'); });
      holder.appendChild(ppt);
    }
    if(!hasGfx){
      var gfx = document.createElement('button');
      gfx.textContent = 'Graphics';
      gfx.className = 'sub-button crv-gfx-btn';
      gfx.addEventListener('click', function(){ clickOriginalModeButton('Graphics'); });
      holder.appendChild(gfx);
    }
  }

  function createDrawer(){
    var actions = document.querySelector('.option-buttons-wrapper');
    var holder = document.querySelector('.sub-buttons-container');
    if(!actions && !holder) return;

    var exists = document.querySelector('.crv-drawer');
    var drawer = exists || document.createElement('div');
    if(!exists){
      drawer.className = 'crv-drawer';
      var btn = document.createElement('button');
      btn.className = 'crv-drawer-toggle';
      btn.setAttribute('aria-expanded','false');
      btn.innerHTML = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none"><path d="M6 14l6-6 6 6" stroke="#5f4a3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
      var content = document.createElement('div');
      content.className = 'crv-drawer-content';

      var anchor = actions || holder;
      var parent = anchor.parentNode;
      parent.insertBefore(drawer, anchor);
      drawer.appendChild(btn);
      drawer.appendChild(content);

      function setOpen(open){
        if(open){
          drawer.classList.add('crv-open');
          btn.setAttribute('aria-expanded','true');
          try { content.style.maxHeight = content.scrollHeight + 'px'; } catch(e){}
        } else {
          drawer.classList.remove('crv-open');
          btn.setAttribute('aria-expanded','false');
          try { content.style.maxHeight = '0px'; } catch(e){}
        }
      }
      btn.addEventListener('click', function(){
        setOpen(!drawer.classList.contains('crv-open'));
      });
      setOpen(false);
    }

    var contentNode = drawer.querySelector('.crv-drawer-content');
    function moveIfNeeded(el){
      if(!el) return;
      if(!contentNode.contains(el)) contentNode.appendChild(el);
    }
    moveIfNeeded(actions);
    moveIfNeeded(holder);

    try { contentNode.style.maxHeight = drawer.classList.contains('crv-open') ? (contentNode.scrollHeight + 'px') : '0px'; } catch(e){}
  }

  var domainQuestions = {
    'SalesMarketing': {
      'Auto': '[Flash] show me sales and marketing performance analysis for the past 3 months',
      'DataReports': '[DataReports - SalesMarketing] generate comprehensive sales and marketing report for the past 3 months',
      'Charts': '[Charts - Bar] visualize sales and marketing trends for the past 3 months',
      'Graphics': '[Graphics] create sales and marketing infographic for the past 3 months',
      'Excel': '[Excel - Sales] export sales and marketing data for the past 3 months',
      'PPT': '[PPT] create sales and marketing presentation for the past 3 months'
    },
    'ProductPerformance': {
      'Auto': '[Flash] analyze product performance and sales trends for the past 3 months',
      'DataReports': '[DataReports - ProductPerformance] generate product performance report for the past 3 months',
      'Charts': '[Charts - Bar] visualize top performing products for the past 3 months',
      'Graphics': '[Graphics] create product performance infographic for the past 3 months',
      'Excel': '[Excel - Sales] export product performance data for the past 3 months',
      'PPT': '[PPT] create product performance presentation for the past 3 months'
    },
    'CustomerAnalysis': {
      'Auto': '[Flash] analyze customer data and show trends for the past 3 months',
      'DataReports': '[DataReports - CustomerAnalysis] generate comprehensive customer analysis report for the past 3 months',
      'Charts': '[Charts - Pie] visualize customer distribution and trends for the past 3 months',
      'Graphics': '[Graphics] create customer analysis infographic for the past 3 months',
      'Excel': '[Excel - Customer] export customer analysis data for the past 3 months',
      'PPT': '[PPT] create customer analysis presentation for the past 3 months'
    },
    'HR': {
      'Auto': '[Flash] show me HR analytics including employee performance and attendance for the past 3 months',
      'DataReports': '[DataReports - HR] generate comprehensive HR report for the past 3 months',
      'Charts': '[Charts - Bar] visualize employee performance and attendance for the past 3 months',
      'Graphics': '[Graphics] create HR analytics infographic for the past 3 months',
      'Excel': '[Excel - HR] export HR data for the past 3 months',
      'PPT': '[PPT] create HR presentation for the past 3 months'
    },
    'Finance': {
      'Auto': '[Flash] what is the total revenue and expenses for the past 3 months',
      'DataReports': '[DataReports - Finance] generate comprehensive financial report for the past 3 months',
      'Charts': '[Charts - Line] visualize revenue and expenses trends for the past 3 months',
      'Graphics': '[Graphics] create financial analysis infographic for the past 3 months',
      'Excel': '[Excel - Finance] export financial data for the past 3 months',
      'PPT': '[PPT] create financial presentation for the past 3 months'
    }
  };

  function getSelectedMode(){
    try { return localStorage.getItem('selectedOption') || 'Auto'; } catch(e){ return 'Auto'; }
  }

  function fillInputField(question){
    var selectors = [
      'textarea.message-input',
      'textarea.centered-message-input',
      'textarea[placeholder*="Ask a question"]',
      'textarea[placeholder*="enterprise data"]'
    ];
    var input = null;
    for(var i=0;i<selectors.length;i++){
      var el = document.querySelector(selectors[i]);
      if(el){ input = el; break; }
    }
    if(!input) return false;
    try {
      var d = Object.getOwnPropertyDescriptor(window.HTMLTextAreaElement.prototype,'value');
      if(d && typeof d.set === 'function') d.set.call(input, question); else input.value = question;
      var ev1 = new Event('input',{bubbles:true});
      var ev2 = new Event('change',{bubbles:true});
      input.dispatchEvent(ev1); input.dispatchEvent(ev2);
      input.focus();
      if(input.style){ input.style.height='auto'; input.style.height=Math.min(input.scrollHeight,120)+'px'; }
      return true;
    } catch(e){ return false; }
  }

  var domainLabelToId = {
    'Sales & Marketing':'SalesMarketing',
    'Product Performance':'ProductPerformance',
    'Customer Analysis':'CustomerAnalysis',
    'HR':'HR',
    'Finance':'Finance'
  };

  function attachDomainHandlers(){
    var container = document.querySelector('.sub-buttons-container');
    if(!container) return false;
    var buttons = container.querySelectorAll('.sub-button');
    var attached = 0;
    for(var i=0;i<buttons.length;i++){
      var btn = buttons[i];
      var text = (btn.textContent||'').trim();
      var id = domainLabelToId[text];
      if(id && !btn.hasAttribute('data-handler-attached')){
        btn.setAttribute('data-handler-attached','true');
        btn.setAttribute('data-domain', id);
        btn.addEventListener('click', function(e){
          e.stopPropagation();
          var did = this.getAttribute('data-domain');
          var mode = getSelectedMode();
          var qs = domainQuestions[did];
          if(!qs) return;
          var q = qs[mode] || qs['Auto'];
          fillInputField(q);
        }, true);
        attached++;
      }
    }
    return attached>0;
  }

  function ensureFixedInput(){
    var wrapper = document.querySelector('.centered-input-wrapper') || document.querySelector('.centered-input-area');
    var root = document.getElementById('root') || document.body;

    function updatePaddingByHeight(el){
      var h = 56;
      try { h = Math.max(56, el.offsetHeight || 0); } catch(e){}
      document.documentElement.style.setProperty('--crv-input-height', h + 'px');
    }

    function fixWrapper(w){
      if(!w) return false;
      if(!w.classList.contains('crv-mobile-input-wrapper-fixed')) w.classList.add('crv-mobile-input-wrapper-fixed');
      updatePaddingByHeight(w);
      function onChange(){ updatePaddingByHeight(w); }
      window.addEventListener('resize', onChange);
      if(window.visualViewport && typeof window.visualViewport.addEventListener === 'function'){
        window.visualViewport.addEventListener('resize', onChange);
      }
      return true;
    }

    function removeLowerDuplicate(){
      var candidates = [
        'textarea.message-input',
        'textarea.centered-message-input',
        'textarea[placeholder*="Ask a question"]',
        'textarea[placeholder*="enterprise data"]'
      ];
      for(var i=0;i<candidates.length;i++){
        var el = document.querySelector(candidates[i]);
        if(!el) continue;
        // If the textarea is not inside the fixed wrapper, remove/hide it
        var insideWrapper = wrapper && wContains(wrapper, el);
        if(!insideWrapper){
          try { el.remove(); }
          catch(e){ try { el.style.display = 'none'; el.classList.add('crv-hidden-input-original'); } catch(_){} }
        }
      }
    }

    function wContains(w, el){
      try { return w && (w === el || w.contains(el)); } catch(e){ return false; }
    }

    // First try with current DOM
    var ok = fixWrapper(wrapper);
    removeLowerDuplicate();

    // Observe and enforce
    var mo = new MutationObserver(function(){
      var w = document.querySelector('.centered-input-wrapper') || document.querySelector('.centered-input-area');
      if(fixWrapper(w)) updatePaddingByHeight(w);
      removeLowerDuplicate();
    });
    mo.observe(root, { childList: true, subtree: true });
  }

  function init(){
    // wait for app content render
    var start = Date.now();
    var timer = setInterval(function(){
      var sb = findSidebar();
      if(sb){
        clearInterval(timer);
        createHistoryUI(sb);
        ensureSecondRowButtons();
        ensureFixedInput();
        attachDomainHandlers();
        createDrawer();
      } else if(Date.now() - start > 6000){
        clearInterval(timer);
      }
    }, 200);

    // 观察 DOM，当顶端按钮区域变动时，确保 Excel/PPT 仍在其下方
    var observer = new MutationObserver(function(){
      ensureSecondRowButtons();
      attachDomainHandlers();
      createDrawer();
    });
    observer.observe(document.body, { childList: true, subtree: true });
  }

  ready(init);
})();