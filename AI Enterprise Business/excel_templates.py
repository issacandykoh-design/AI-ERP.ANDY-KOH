# Excel Templates Configuration
# 为每个Excel业务部分定义专门的处理模板

EXCEL_TEMPLATES = {
    "Sales": {
        "name": "Sales Analysis",
        "template": """
# Sales Analysis Report

## Sales Overview
{sales_overview}

## Revenue Analysis
{revenue_analysis}

## Sales Performance Metrics
{performance_metrics}

## Customer Acquisition
{customer_acquisition}

## Sales Trends
{sales_trends}

## Recommendations
{recommendations}

---
*Sales analysis generated on {date}*
        """,
        "prompt_enhancement": "Please provide a comprehensive sales analysis. Focus on revenue trends, sales performance metrics, customer acquisition data, and actionable insights for sales improvement. Include Excel-friendly data formats and visualization recommendations."
    },
    
    "Finance": {
        "name": "Financial Analysis",
        "template": """
# Financial Analysis Report

## Financial Overview
{financial_overview}

## Revenue & Expenses
{revenue_expenses}

## Profitability Analysis
{profitability_analysis}

## Cash Flow Analysis
{cash_flow}

## Financial Ratios
{financial_ratios}

## Budget vs Actual
{budget_actual}

## Financial Recommendations
{financial_recommendations}

---
*Financial analysis generated on {date}*
        """,
        "prompt_enhancement": "Please provide detailed financial analysis including revenue, expenses, profitability, cash flow, and key financial ratios. Focus on creating Excel-compatible financial models and reports with clear metrics and actionable insights."
    },
    
    "Customer": {
        "name": "Customer Analysis",
        "template": """
# Customer Analysis Report

## Customer Demographics
{customer_demographics}

## Customer Behavior Analysis
{customer_behavior}

## Customer Segmentation
{customer_segmentation}

## Customer Lifetime Value
{customer_ltv}

## Customer Satisfaction
{customer_satisfaction}

## Retention Analysis
{retention_analysis}

## Customer Insights
{customer_insights}

---
*Customer analysis generated on {date}*
        """,
        "prompt_enhancement": "Please provide comprehensive customer analysis including demographics, behavior patterns, segmentation, lifetime value, and retention metrics. Focus on actionable customer insights and Excel-compatible data visualization."
    },
    
    "Inventory": {
        "name": "Inventory Management",
        "template": """
# Inventory Management Report

## Inventory Overview
{inventory_overview}

## Stock Levels Analysis
{stock_levels}

## Inventory Turnover
{inventory_turnover}

## Product Performance
{product_performance}

## Demand Forecasting
{demand_forecasting}

## Inventory Optimization
{inventory_optimization}

## Recommendations
{inventory_recommendations}

---
*Inventory analysis generated on {date}*
        """,
        "prompt_enhancement": "Please provide detailed inventory management analysis including stock levels, turnover rates, product performance, and demand forecasting. Focus on inventory optimization strategies and Excel-compatible tracking systems."
    },
    
    "HR": {
        "name": "HR Analytics",
        "template": """
# HR Analytics Report

## Employee Overview
{employee_overview}

## Performance Metrics
{performance_metrics}

## Recruitment Analysis
{recruitment_analysis}

## Employee Retention
{employee_retention}

## Training & Development
{training_development}

## Compensation Analysis
{compensation_analysis}

## HR Recommendations
{hr_recommendations}

---
*HR analytics generated on {date}*
        """,
        "prompt_enhancement": "Please provide comprehensive HR analytics including employee performance, recruitment metrics, retention analysis, and compensation data. Focus on actionable HR insights and Excel-compatible workforce analytics."
    }
}

def get_excel_template_by_sub_option(sub_option_id):
    """获取指定Excel子选项的模板配置"""
    return EXCEL_TEMPLATES.get(sub_option_id)

def get_all_excel_template_names():
    """获取所有Excel模板名称"""
    return {key: value["name"] for key, value in EXCEL_TEMPLATES.items()}

def enhance_prompt_with_excel_template(sub_option_id, original_prompt):
    """使用Excel模板增强用户提示"""
    template_config = get_excel_template_by_sub_option(sub_option_id)
    if template_config:
        enhanced_prompt = f"{template_config['prompt_enhancement']}\n\nUser Question: {original_prompt}"
        return enhanced_prompt
    return original_prompt