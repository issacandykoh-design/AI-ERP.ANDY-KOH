"""
数据分析器模块
负责将SQL查询结果发送给LLM进行智能分析，生成数据洞察和建议
"""

import pandas as pd
from typing import Dict, Any, Optional, List
import json
from abc import ABC, abstractmethod
import numpy as np
from datetime import datetime, date


def safe_json_dumps(obj, **kwargs):
    """安全的JSON序列化函数，处理Pandas特殊类型"""
    def default_serializer(o):
        if isinstance(o, (pd.Timestamp, datetime, date)):
            return str(o)
        elif isinstance(o, np.integer):
            return int(o)
        elif isinstance(o, np.floating):
            return float(o)
        elif isinstance(o, np.ndarray):
            return o.tolist()
        elif pd.isna(o):
            return None
        else:
            return str(o)
    
    return json.dumps(obj, default=default_serializer, **kwargs)


class DataAnalyzer(ABC):
    """数据分析器基类"""
    
    def __init__(self, config: Dict[str, Any] = None):
        self.config = config or {}
        self.max_rows_for_analysis = self.config.get("max_rows_for_analysis", 100)
        self.language = self.config.get("language", "中文")
    
    @abstractmethod
    def submit_prompt(self, prompt: str, **kwargs) -> str:
        """提交提示词给LLM，需要在子类中实现"""
        pass
    
    def analyze_data(
        self, 
        question: str, 
        sql: str, 
        df: pd.DataFrame,
        context: Optional[str] = None
    ) -> Dict[str, Any]:
        """
        分析数据并生成智能报告
        
        Args:
            question: 用户的原始问题
            sql: 生成的SQL查询
            df: 查询结果数据
            context: 额外的上下文信息
            
        Returns:
            包含分析结果的字典
        """
        if df is None or df.empty:
            return {
                "analysis": "查询未返回任何数据，无法进行分析。",
                "insights": [],
                "recommendations": [],
                "summary": "数据为空"
            }
        
        # 准备数据摘要
        data_summary = self._prepare_data_summary(df)
        
        # 构建分析提示词
        analysis_prompt = self._build_analysis_prompt(
            question, sql, data_summary, context
        )
        
        # 调用LLM进行分析
        try:
            analysis_result = self.submit_prompt(analysis_prompt)
            return self._parse_analysis_result(analysis_result)
        except Exception as e:
            return {
                "analysis": f"分析过程中出现错误: {str(e)}",
                "insights": [],
                "recommendations": [],
                "summary": "分析失败"
            }
    
    def _prepare_data_summary(self, df: pd.DataFrame) -> Dict[str, Any]:
        """准备数据摘要信息"""
        # 限制分析的行数，避免token过多
        sample_df = df.head(self.max_rows_for_analysis)
        
        # 安全地转换样本数据
        sample_records = []
        for record in sample_df.to_dict('records'):
            safe_record = {}
            for key, value in record.items():
                if pd.isna(value):
                    safe_record[key] = None
                elif isinstance(value, (pd.Timestamp, datetime, date)):
                    safe_record[key] = str(value)
                elif isinstance(value, (np.integer, np.floating)):
                    safe_record[key] = value.item()
                else:
                    safe_record[key] = value
            sample_records.append(safe_record)
        
        summary = {
            "total_rows": len(df),
            "columns": list(df.columns),
            "data_types": {col: str(dtype) for col, dtype in df.dtypes.to_dict().items()},
            "sample_data": sample_records,
            "basic_stats": {}
        }
        
        # 添加数值列的基本统计信息
        numeric_columns = df.select_dtypes(include=['number']).columns
        if len(numeric_columns) > 0:
            stats_dict = df[numeric_columns].describe().to_dict()
            # 安全地转换统计数据
            safe_stats = {}
            for col, stats in stats_dict.items():
                safe_stats[col] = {}
                for stat_name, stat_value in stats.items():
                    if pd.isna(stat_value):
                        safe_stats[col][stat_name] = None
                    elif isinstance(stat_value, (np.integer, np.floating)):
                        safe_stats[col][stat_name] = stat_value.item()
                    else:
                        safe_stats[col][stat_name] = stat_value
            summary["basic_stats"] = safe_stats
        
        # 添加分类列的信息
        categorical_columns = df.select_dtypes(include=['object', 'category']).columns
        if len(categorical_columns) > 0:
            summary["categorical_info"] = {}
            for col in categorical_columns:
                summary["categorical_info"][col] = {
                    "unique_count": df[col].nunique(),
                    "top_values": df[col].value_counts().head(5).to_dict()
                }
        
        return summary
    
    def _build_analysis_prompt(
        self, 
        question: str, 
        sql: str, 
        data_summary: Dict[str, Any],
        context: Optional[str] = None
    ) -> str:
        """构建数据分析提示词"""
        
        prompt = f"""
你是一位专业的数据分析师。请基于以下信息进行深入的数据分析：

**用户问题：** {question}

**执行的SQL查询：**
```sql
{sql}
```

**数据概览：**
- 总行数：{data_summary['total_rows']}
- 列名：{', '.join(data_summary['columns'])}
- 数据类型：{safe_json_dumps(data_summary['data_types'], ensure_ascii=False, indent=2)}

**样本数据：**
{safe_json_dumps(data_summary['sample_data'][:10], ensure_ascii=False, indent=2)}

**统计信息：**
{safe_json_dumps(data_summary.get('basic_stats', {}), ensure_ascii=False, indent=2)}

**分类数据信息：**
{safe_json_dumps(data_summary.get('categorical_info', {}), ensure_ascii=False, indent=2)}
"""

        if context:
            prompt += f"\n**额外上下文：**\n{context}"

        prompt += f"""

请用{self.language}提供一份详细的数据分析报告，包含以下部分：

1. **数据概述**：简要描述数据的基本情况
2. **关键发现**：从数据中发现的重要模式、趋势或异常
3. **深度洞察**：对数据背后含义的深入分析
4. **业务建议**：基于分析结果提出的具体建议
5. **总结**：简洁的结论总结

请以JSON格式返回结果：
{{
    "summary": "数据概述",
    "key_findings": ["发现1", "发现2", "发现3"],
    "insights": ["洞察1", "洞察2", "洞察3"],
    "recommendations": ["建议1", "建议2", "建议3"],
    "conclusion": "总结"
}}

请确保分析深入、专业，并提供可操作的建议。
"""
        
        return prompt
    
    def _parse_analysis_result(self, analysis_result: str) -> Dict[str, Any]:
        """解析LLM返回的分析结果"""
        try:
            # 尝试解析JSON格式的结果
            if analysis_result.strip().startswith('{'):
                return json.loads(analysis_result)
            
            # 如果不是JSON格式，尝试提取内容
            lines = analysis_result.strip().split('\n')
            result = {
                "summary": "",
                "key_findings": [],
                "insights": [],
                "recommendations": [],
                "conclusion": ""
            }
            
            current_section = None
            for line in lines:
                line = line.strip()
                if not line:
                    continue
                    
                if "数据概述" in line or "概述" in line:
                    current_section = "summary"
                elif "关键发现" in line or "发现" in line:
                    current_section = "key_findings"
                elif "深度洞察" in line or "洞察" in line:
                    current_section = "insights"
                elif "业务建议" in line or "建议" in line:
                    current_section = "recommendations"
                elif "总结" in line or "结论" in line:
                    current_section = "conclusion"
                elif current_section:
                    if current_section in ["key_findings", "insights", "recommendations"]:
                        if line.startswith(('-', '•', '*', '1.', '2.', '3.')):
                            result[current_section].append(line.lstrip('-•*123. '))
                        else:
                            result[current_section].append(line)
                    else:
                        result[current_section] += line + " "
            
            return result
            
        except Exception as e:
            # 如果解析失败，返回原始文本
            return {
                "summary": analysis_result,
                "key_findings": [],
                "insights": [],
                "recommendations": [],
                "conclusion": "分析完成"
            }


class ReportGenerator:
    """报告生成器，负责格式化分析结果"""
    
    def __init__(self, config: Dict[str, Any] = None):
        self.config = config or {}
        self.language = self.config.get("language", "中文")
    
    def generate_report(
        self, 
        question: str,
        analysis_result: Dict[str, Any],
        include_metadata: bool = True
    ) -> str:
        """
        生成格式化的分析报告
        
        Args:
            question: 用户的原始问题
            analysis_result: 分析结果
            include_metadata: 是否包含元数据信息
            
        Returns:
            格式化的报告文本
        """
        report = []
        
        # 报告标题
        report.append("=" * 60)
        report.append("📊 数据分析报告")
        report.append("=" * 60)
        report.append("")
        
        # 用户问题
        report.append(f"🔍 **分析问题：** {question}")
        report.append("")
        
        # 数据概述
        if analysis_result.get("summary"):
            report.append("📋 **数据概述**")
            report.append(analysis_result["summary"])
            report.append("")
        
        # 关键发现
        if analysis_result.get("key_findings"):
            report.append("🔎 **关键发现**")
            for i, finding in enumerate(analysis_result["key_findings"], 1):
                report.append(f"   {i}. {finding}")
            report.append("")
        
        # 深度洞察
        if analysis_result.get("insights"):
            report.append("💡 **深度洞察**")
            for i, insight in enumerate(analysis_result["insights"], 1):
                report.append(f"   {i}. {insight}")
            report.append("")
        
        # 业务建议
        if analysis_result.get("recommendations"):
            report.append("🎯 **业务建议**")
            for i, recommendation in enumerate(analysis_result["recommendations"], 1):
                report.append(f"   {i}. {recommendation}")
            report.append("")
        
        # 结论
        if analysis_result.get("conclusion"):
            report.append("📝 **总结**")
            report.append(analysis_result["conclusion"])
            report.append("")
        
        report.append("=" * 60)
        
        return "\n".join(report)
    
    def generate_json_report(
        self, 
        question: str,
        analysis_result: Dict[str, Any],
        sql: str = None,
        execution_time: float = None
    ) -> Dict[str, Any]:
        """生成JSON格式的报告"""
        report = {
            "question": question,
            "analysis": analysis_result,
            "metadata": {
                "generated_at": datetime.now().isoformat(),
                "language": self.language
            }
        }
        
        if sql:
            report["metadata"]["sql"] = sql
        if execution_time:
            report["metadata"]["execution_time"] = execution_time
            
        return report