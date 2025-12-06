# Data Reports Templates Configuration
# 为每个Data Reports子按钮定义专门的回答模板
import random

DATA_REPORTS_TEMPLATES = {
    "SalesMarketing": {
        "name": "Sales & Marketing Analysis",
        "template": """
# Sales & Marketing Analysis Report

## Executive Summary
{executive_summary}

## Key Performance Indicators (KPIs)
### Sales Metrics
- Total Revenue: {total_revenue}
- Sales Growth Rate: {sales_growth}
- Average Order Value: {avg_order_value}
- Conversion Rate: {conversion_rate}

### Marketing Metrics
- Customer Acquisition Cost (CAC): {cac}
- Return on Marketing Investment (ROMI): {romi}
- Lead Generation: {lead_generation}
- Brand Awareness: {brand_awareness}

## Sales Performance Analysis
{sales_analysis}

## Marketing Campaign Effectiveness
{marketing_analysis}

## Customer Segmentation
{customer_segmentation}

## Recommendations
{recommendations}

## Action Items
{action_items}

---
*Report generated on {date}*
        """,
        "prompt_enhancements": [
            "Please provide a comprehensive sales and marketing analysis including KPIs, performance metrics, customer segmentation, and actionable recommendations. Format the response as a professional business report.",
            "Conduct a detailed sales and marketing performance review with focus on revenue trends, conversion rates, customer acquisition costs, and strategic growth opportunities. Present findings in a structured business format.",
            "Analyze sales and marketing data to identify key performance drivers, market opportunities, customer behavior patterns, and optimization strategies. Deliver insights in a professional report format.",
            "Perform an in-depth sales and marketing assessment covering revenue analysis, campaign effectiveness, customer journey optimization, and competitive positioning. Structure as an executive-level business report.",
            "Generate a strategic sales and marketing analysis examining performance metrics, market penetration, customer lifetime value, and ROI optimization. Format as a comprehensive business intelligence report.",
            "Evaluate sales and marketing performance through data-driven analysis of conversion funnels, customer acquisition strategies, revenue growth patterns, and market share dynamics. Present in professional report format.",
            "Provide a thorough sales and marketing evaluation focusing on pipeline analysis, customer segmentation strategies, campaign performance, and revenue optimization opportunities. Structure as a business strategy report.",
            "Analyze sales and marketing effectiveness by examining lead generation quality, customer retention rates, pricing strategies, and market positioning. Deliver findings in a detailed business report format.",
            "Conduct a comprehensive sales and marketing review covering territory performance, channel effectiveness, customer satisfaction metrics, and growth potential analysis. Format as an executive summary report.",
            "Examine sales and marketing operations through performance benchmarking, customer behavior analysis, competitive intelligence, and strategic recommendations. Present as a professional business analysis.",
            "Assess sales and marketing impact via revenue attribution analysis, customer journey mapping, conversion optimization insights, and market opportunity identification. Structure as a strategic business report.",
            "Perform sales and marketing analytics covering demand forecasting, customer acquisition efficiency, brand performance metrics, and tactical optimization strategies. Format as a comprehensive business review.",
            "Analyze sales and marketing synergies through cross-channel performance evaluation, customer lifecycle analysis, competitive advantage assessment, and growth strategy recommendations. Present in executive report format.",
            "Evaluate sales and marketing ROI by examining investment allocation effectiveness, customer value optimization, market expansion opportunities, and performance improvement strategies. Structure as a business intelligence report.",
            "Conduct strategic sales and marketing analysis focusing on market dynamics, customer engagement metrics, sales velocity optimization, and long-term growth planning. Deliver as a professional business assessment."
        ]
    },
    
    "ProductPerformance": {
        "name": "Product Performance Analysis",
        "template": """
# Product Performance Analysis Report

## Executive Summary
{executive_summary}

## Product Portfolio Overview
{portfolio_overview}

## Top Performing Products
{top_products}

## Product Performance Metrics
### Sales Performance
- Best Selling Products: {best_sellers}
- Revenue by Product Category: {revenue_by_category}
- Product Profitability: {profitability}

### Market Performance
- Market Share: {market_share}
- Competitive Position: {competitive_position}
- Customer Satisfaction: {customer_satisfaction}

## Product Lifecycle Analysis
{lifecycle_analysis}

## Inventory Analysis
{inventory_analysis}

## Product Development Insights
{development_insights}

## Recommendations
{recommendations}

## Strategic Actions
{strategic_actions}

---
*Report generated on {date}*
        """,
        "prompt_enhancements": [
            "Please provide a detailed product performance analysis including sales metrics, market position, inventory status, and product development insights. Focus on data-driven recommendations for product strategy.",
            "Conduct a comprehensive product portfolio evaluation covering sales performance, profitability analysis, market share assessment, and strategic product roadmap recommendations. Present in professional business format.",
            "Analyze product performance data to identify top performers, underperforming items, market opportunities, and optimization strategies. Structure findings as an executive product strategy report.",
            "Perform an in-depth product analysis examining revenue contribution, customer satisfaction scores, competitive positioning, and lifecycle management strategies. Format as a strategic business assessment.",
            "Generate a thorough product performance review focusing on sales velocity, margin analysis, inventory turnover, and product development priorities. Deliver as a comprehensive business intelligence report.",
            "Evaluate product portfolio effectiveness through performance benchmarking, market penetration analysis, customer feedback integration, and innovation opportunity identification. Present in executive report format.",
            "Provide a strategic product analysis covering category performance, pricing optimization, demand forecasting, and product mix recommendations. Structure as a detailed business strategy document.",
            "Assess product performance via sales trend analysis, profitability evaluation, market positioning review, and product lifecycle optimization strategies. Format as a professional business report.",
            "Conduct product portfolio analytics examining cross-selling opportunities, seasonal performance patterns, competitive advantages, and strategic product investments. Present as an executive summary.",
            "Analyze product effectiveness through customer adoption metrics, revenue per product analysis, market share dynamics, and product development ROI assessment. Structure as a business intelligence report.",
            "Examine product performance indicators including sales conversion rates, customer retention by product, pricing elasticity, and product innovation impact. Deliver in comprehensive report format.",
            "Perform product strategy analysis covering market demand assessment, competitive product comparison, profitability optimization, and product line expansion opportunities. Format as executive business review.",
            "Evaluate product success metrics through sales performance tracking, customer satisfaction correlation, inventory efficiency analysis, and strategic product positioning. Present as professional business assessment.",
            "Conduct comprehensive product analysis focusing on revenue attribution, market penetration effectiveness, product cannibalization risks, and portfolio optimization strategies. Structure as strategic business report.",
            "Assess product portfolio performance via demand pattern analysis, profitability segmentation, competitive differentiation evaluation, and future product development recommendations. Format as executive strategy document."
        ]
    },
    
    "CustomerAnalysis": {
        "name": "Customer Analysis Report",
        "template": """
# Customer Analysis Report

## Executive Summary
{executive_summary}

## Customer Demographics
{demographics}

## Customer Segmentation
### Behavioral Segments
{behavioral_segments}

### Value-Based Segments
{value_segments}

### Geographic Distribution
{geographic_distribution}

## Customer Lifetime Value (CLV)
{clv_analysis}

## Customer Acquisition & Retention
### Acquisition Metrics
- New Customer Rate: {new_customer_rate}
- Acquisition Channels: {acquisition_channels}
- Cost per Acquisition: {cpa}

### Retention Metrics
- Customer Retention Rate: {retention_rate}
- Churn Rate: {churn_rate}
- Repeat Purchase Rate: {repeat_purchase_rate}

## Customer Satisfaction Analysis
{satisfaction_analysis}

## Customer Journey Insights
{journey_insights}

## Recommendations
{recommendations}

## Customer Experience Improvements
{cx_improvements}

---
*Report generated on {date}*
        """,
        "prompt_enhancements": [
            "Please provide a comprehensive customer analysis including demographics, segmentation, lifetime value, acquisition/retention metrics, and customer experience insights. Include actionable recommendations for customer relationship management.",
            "Conduct an in-depth customer analytics review covering demographic profiling, purchase behavior analysis, retention metrics, and customer journey optimization strategies. Present as a strategic CRM report.",
            "Analyze customer data to identify key segments, behavioral trends, satisfaction drivers, and churn prevention opportunities. Structure findings as a comprehensive customer intelligence report.",
            "Perform detailed customer analysis examining acquisition patterns, lifetime value calculations, engagement metrics, and personalization opportunities. Format as a strategic customer management assessment.",
            "Generate thorough customer insights covering demographic analysis, purchase frequency patterns, loyalty program effectiveness, and customer experience optimization. Deliver as a professional business intelligence report.",
            "Evaluate customer portfolio through segmentation analysis, behavioral clustering, satisfaction correlation studies, and retention strategy recommendations. Present in executive customer strategy format.",
            "Provide strategic customer analysis focusing on demographic trends, buying behavior patterns, customer journey mapping, and relationship management optimization. Structure as detailed business strategy document.",
            "Assess customer performance via engagement analytics, purchase pattern analysis, satisfaction benchmarking, and customer development opportunities. Format as comprehensive customer intelligence report.",
            "Conduct customer analytics examining cross-selling potential, seasonal behavior patterns, geographic distribution insights, and customer experience enhancement strategies. Present as executive customer review.",
            "Analyze customer effectiveness through acquisition cost analysis, retention rate optimization, customer satisfaction correlation, and lifetime value maximization strategies. Structure as business intelligence assessment.",
            "Examine customer relationship indicators including engagement frequency, purchase conversion rates, loyalty program participation, and customer advocacy potential. Deliver in comprehensive analytics format.",
            "Perform customer strategy analysis covering market penetration assessment, customer needs evaluation, satisfaction improvement opportunities, and relationship deepening tactics. Format as executive customer review.",
            "Evaluate customer success metrics through behavioral analysis, demographic correlation studies, satisfaction tracking, and customer development planning. Present as professional customer management assessment.",
            "Conduct comprehensive customer evaluation focusing on segment performance, behavioral prediction modeling, satisfaction optimization, and customer experience personalization strategies. Structure as strategic customer report.",
            "Assess customer portfolio performance via demographic analysis, behavioral segmentation, satisfaction measurement, and customer relationship optimization recommendations. Format as executive customer strategy document."
        ]
    },
    
    "HR": {
        "name": "Human Resources Analytics Report",
        "template": """
# Human Resources Analytics Report

## Executive Summary
{executive_summary}

## Workforce Overview
{workforce_overview}

## Employee Performance Metrics
### Productivity Indicators
- Employee Productivity: {productivity}
- Performance Ratings: {performance_ratings}
- Goal Achievement: {goal_achievement}

### Engagement Metrics
- Employee Satisfaction: {satisfaction}
- Engagement Score: {engagement_score}
- Net Promoter Score (eNPS): {enps}

## Talent Acquisition & Retention
### Recruitment Metrics
- Time to Hire: {time_to_hire}
- Cost per Hire: {cost_per_hire}
- Quality of Hire: {quality_of_hire}

### Retention Analysis
- Employee Turnover Rate: {turnover_rate}
- Retention Rate by Department: {retention_by_dept}
- Exit Interview Insights: {exit_insights}

## Training & Development
{training_analysis}

## Compensation & Benefits Analysis
{compensation_analysis}

## Diversity & Inclusion Metrics
{diversity_metrics}

## Recommendations
{recommendations}

## HR Strategic Initiatives
{strategic_initiatives}

---
*Report generated on {date}*
        """,
        "prompt_enhancements": [
            "Please provide a comprehensive HR analytics report including workforce metrics, employee performance, talent acquisition/retention, training effectiveness, and diversity insights. Focus on data-driven HR strategy recommendations.",
            "Conduct an in-depth human resources analytics review covering employee performance evaluation, recruitment effectiveness, retention strategies, and organizational development initiatives. Present as a strategic HR management report.",
            "Analyze HR data to identify talent acquisition trends, employee engagement drivers, performance optimization opportunities, and workforce planning strategies. Structure findings as a comprehensive human capital intelligence report.",
            "Perform detailed HR analysis examining compensation benchmarking, employee satisfaction metrics, training effectiveness, and succession planning initiatives. Format as a strategic workforce management assessment.",
            "Generate thorough HR insights covering demographic analysis, performance correlation studies, employee development tracking, and organizational culture enhancement. Deliver as a professional human resources intelligence report.",
            "Evaluate workforce effectiveness through talent pipeline analysis, employee engagement correlation studies, retention strategy assessment, and leadership development recommendations. Present in executive HR strategy format.",
            "Provide strategic HR analysis focusing on recruitment optimization, performance management enhancement, employee experience improvement, and organizational capability building. Structure as detailed human capital strategy document.",
            "Assess HR performance via talent acquisition analytics, employee satisfaction benchmarking, training ROI evaluation, and workforce productivity optimization. Format as comprehensive HR intelligence report.",
            "Conduct HR analytics examining diversity and inclusion metrics, employee career progression patterns, compensation equity analysis, and workplace culture enhancement strategies. Present as executive HR review.",
            "Analyze HR effectiveness through recruitment funnel optimization, employee retention correlation analysis, performance management evaluation, and talent development planning. Structure as business intelligence HR assessment.",
            "Examine human capital indicators including employee engagement frequency, performance improvement tracking, training completion rates, and leadership pipeline development. Deliver in comprehensive HR analytics format.",
            "Perform HR strategy analysis covering workforce planning assessment, employee needs evaluation, satisfaction improvement opportunities, and organizational development tactics. Format as executive HR review.",
            "Evaluate HR success metrics through talent management analysis, employee experience correlation studies, performance tracking, and workforce development planning. Present as professional HR management assessment.",
            "Conduct comprehensive HR evaluation focusing on talent acquisition effectiveness, employee engagement optimization, performance management enhancement, and organizational culture development strategies. Structure as strategic HR report.",
            "Assess HR portfolio performance via workforce analytics, employee satisfaction measurement, talent development tracking, and human capital optimization recommendations. Format as executive HR strategy document."
        ]
    },
    
    "Finance": {
        "name": "Financial Analysis Report",
        "template": """
# Financial Analysis Report

## Executive Summary
{executive_summary}

## Financial Performance Overview
{financial_overview}

## Revenue Analysis
### Revenue Streams
- Primary Revenue Sources: {revenue_sources}
- Revenue Growth Trends: {revenue_trends}
- Seasonal Patterns: {seasonal_patterns}

### Profitability Metrics
- Gross Profit Margin: {gross_margin}
- Operating Profit Margin: {operating_margin}
- Net Profit Margin: {net_margin}
- EBITDA: {ebitda}

## Cost Analysis
{cost_analysis}

## Cash Flow Analysis
### Operating Cash Flow
{operating_cashflow}

### Investment Cash Flow
{investment_cashflow}

### Financing Cash Flow
{financing_cashflow}

## Financial Ratios
### Liquidity Ratios
- Current Ratio: {current_ratio}
- Quick Ratio: {quick_ratio}
- Cash Ratio: {cash_ratio}

### Efficiency Ratios
- Asset Turnover: {asset_turnover}
- Inventory Turnover: {inventory_turnover}
- Receivables Turnover: {receivables_turnover}

## Budget vs Actual Analysis
{budget_analysis}

## Financial Forecasting
{forecasting}

## Risk Assessment
{risk_assessment}

## Recommendations
{recommendations}

## Financial Strategic Actions
{strategic_actions}

---
*Report generated on {date}*
        """,
        "prompt_enhancements": [
            "Please provide a comprehensive financial analysis including revenue/profitability metrics, cash flow analysis, financial ratios, budget variance, and risk assessment. Include strategic financial recommendations and forecasting insights.",
            "Conduct an in-depth financial analytics review covering revenue performance evaluation, cost structure analysis, liquidity assessment, and investment optimization strategies. Present as a strategic financial management report.",
            "Analyze financial data to identify profitability trends, cash flow optimization opportunities, financial ratio benchmarking, and capital allocation strategies. Structure findings as a comprehensive financial intelligence report.",
            "Perform detailed financial analysis examining revenue diversification, margin improvement initiatives, working capital management, and financial risk mitigation. Format as a strategic financial assessment.",
            "Generate thorough financial insights covering budget variance analysis, financial performance tracking, investment ROI evaluation, and financial planning optimization. Deliver as a professional financial intelligence report.",
            "Evaluate financial effectiveness through profitability analysis, cash flow forecasting, financial ratio correlation studies, and capital structure optimization recommendations. Present in executive financial strategy format.",
            "Provide strategic financial analysis focusing on revenue growth optimization, cost management enhancement, financial efficiency improvement, and investment portfolio optimization. Structure as detailed financial strategy document.",
            "Assess financial performance via earnings analysis, cash flow benchmarking, financial ratio evaluation, and capital allocation optimization. Format as comprehensive financial intelligence report.",
            "Conduct financial analytics examining revenue stream diversification, operational efficiency metrics, financial leverage analysis, and strategic investment planning. Present as executive financial review.",
            "Analyze financial effectiveness through budget performance optimization, cash flow correlation analysis, financial ratio evaluation, and strategic financial planning. Structure as business intelligence financial assessment.",
            "Examine financial indicators including revenue growth patterns, profitability improvement tracking, liquidity management efficiency, and financial risk assessment. Deliver in comprehensive financial analytics format.",
            "Perform financial strategy analysis covering budget planning assessment, financial performance evaluation, investment opportunity identification, and financial optimization tactics. Format as executive financial review.",
            "Evaluate financial success metrics through revenue analysis, cost optimization studies, financial ratio tracking, and strategic financial development planning. Present as professional financial management assessment.",
            "Conduct comprehensive financial evaluation focusing on revenue optimization effectiveness, cost management enhancement, financial performance improvement, and strategic financial planning strategies. Structure as strategic financial report.",
            "Assess financial portfolio performance via revenue analytics, profitability measurement, financial efficiency tracking, and capital optimization recommendations. Format as executive financial strategy document."
        ]
    }
}

def get_template_by_sub_option(sub_option_id):
    """获取指定子选项的模板配置"""
    return DATA_REPORTS_TEMPLATES.get(sub_option_id)

def get_all_template_names():
    """获取所有模板名称"""
    return {key: value["name"] for key, value in DATA_REPORTS_TEMPLATES.items()}

def enhance_prompt_with_template(sub_option_id, original_prompt):
    """使用模板增强用户提示"""
    template_config = get_template_by_sub_option(sub_option_id)
    if template_config:
        # 随机选择一个增强提示
        enhancements = template_config.get('prompt_enhancements', [])
        if enhancements:
            enhancement = random.choice(enhancements)
        else:
            enhancement = template_config.get('prompt_enhancement', '')
        enhanced_prompt = f"{enhancement}\n\nUser Question: {original_prompt}"
        return enhanced_prompt
    return original_prompt