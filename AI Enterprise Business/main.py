#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Craveva AI Enterprise Business
Does not depend on ChromaDB, uses OpenAI directly for SQL generation and data analysis
"""

import os
import sys
import sqlite3
import pandas as pd
import json
from datetime import datetime
from dotenv import load_dotenv
from openai import OpenAI
from conversation_manager import ConversationManager
from export_manager import ExportManager, DataFormatter

# Load environment variables
load_dotenv()

class SimpleCoffeeAnalyzer:
    """Craveva AI Enterprise Business"""
    
    def __init__(self):
        """Initialize analyzer"""
        self.db_path = "my_database.db"
        
        # Get API provider from environment
        api_provider = os.getenv('API_PROVIDER', 'openai').lower()
        
        if api_provider == 'deepseek':
            # Configure DEEPSEEK API
            self.client = OpenAI(
                api_key=os.getenv('DEEPSEEK_API_KEY'),
                base_url=os.getenv('DEEPSEEK_BASE_URL', 'https://api.deepseek.com')
            )
            self.model = os.getenv('DEEPSEEK_MODEL', 'deepseek-chat')
            # Optional fast model for Flash mode
            self.fast_model = os.getenv('DEEPSEEK_FAST_MODEL')
        elif api_provider == 'openrouter':
            # Configure OpenRouter API
            self.client = OpenAI(
                api_key=os.getenv('OPENROUTER_API_KEY'),
                base_url=os.getenv('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1')
            )
            self.model = os.getenv('OPENROUTER_MODEL', 'google/gemini-2.0-flash-001')
            self.fast_model = os.getenv('OPENROUTER_FAST_MODEL')
        else:
            # Configure OpenAI (default)
            self.client = OpenAI(api_key=os.getenv('OPENAI_API_KEY'))
            self.model = os.getenv('OPENAI_MODEL', 'gpt-4')
            # Optional fast model for Flash mode (e.g., gpt-4o-mini)
            self.fast_model = os.getenv('OPENAI_FAST_MODEL')
        
        # Initialize MySQL configuration (optional)
        self.mysql_config = {
            'host': os.getenv('MYSQL_HOST', 'localhost'),
            'user': os.getenv('MYSQL_USER', 'root'),
            'password': os.getenv('MYSQL_PASSWORD', ''),
            'database': os.getenv('MYSQL_DATABASE', 'craveva'),
            'port': int(os.getenv('MYSQL_PORT', '3306'))
        } if os.getenv('USE_MYSQL', 'false').lower() == 'true' else None
        
        # Initialize conversation manager
        self.conversation_manager = ConversationManager(self.client, self.model, mysql_config=self.mysql_config)
        
        # Initialize export manager
        self.export_manager = ExportManager()
        
        # Don't create a persistent connection - create new connections as needed
        
        # Database table structure information
        self.table_info = """
        Database Schema:
        
        1. index_1 table (Main sales records table, 3636 records) - 6 COLUMNS:
           - date (TEXT): Transaction date (format: YYYY-MM-DD)
           - datetime (TEXT): Detailed transaction timestamp
           - cash_type (TEXT): Payment method ('card' or 'cash')
           - card (TEXT): Anonymized card number information [UNIQUE TO index_1]
           - money (REAL): Transaction amount (15-40 yuan)
           - coffee_name (TEXT): Coffee product name
        
        2. index_2 table (Cash transaction records table, 262 records) - 5 COLUMNS:
           - date (TEXT): Transaction date
           - datetime (TEXT): Detailed transaction timestamp  
           - cash_type (TEXT): Payment method
           - money (REAL): Transaction amount
           - coffee_name (TEXT): Coffee product name
           [NOTE: index_2 does NOT have a 'card' column]
        
        IMPORTANT: When using UNION operations, you MUST select the same number of columns from both tables.
        Common columns for UNION: date, datetime, cash_type, money, coffee_name
        
        Main products include: Americano with Milk, Latte, Americano, Cappuccino, Cortado, Hot Chocolate, Cocoa, Espresso
        """
    
    def get_schema_info(self):
        """Get database schema information"""
        return self.table_info
    
    def generate_sql(self, question, mode=None):
        """Generate SQL query statement"""
        try:
            model_to_use = self.fast_model if (mode == 'Flash' and getattr(self, 'fast_model', None)) else self.model
            prompt = f"""
            You are a SQL expert. Based on the following database table structure, generate an accurate SQL query statement for the user's question.
            
            {self.table_info}
            
            User question: {question}
            
            Important rules:
            1. Use proper SQLite syntax
            2. CRITICAL: index_1 has 6 columns (date, datetime, cash_type, card, money, coffee_name) while index_2 has 5 columns (date, datetime, cash_type, money, coffee_name)
            3. When combining data from both tables, you MUST handle the column difference properly
            4. Always use valid SQL syntax - avoid syntax errors
            5. Return only the SQL statement without any explanation or markdown formatting
            6. IMPORTANT: Do NOT add LIMIT clauses unless the user specifically requests a limited number of results (e.g., "top 10", "first 5"). Return ALL data by default for complete CSV exports.
            
            Examples of correct SQL patterns:
            - Total count from multiple tables: SELECT (SELECT COUNT(*) FROM index_1) + (SELECT COUNT(*) FROM index_2) AS total_transactions;
            - Correct UNION with matching columns: SELECT date, datetime, cash_type, money, coffee_name FROM index_1 UNION ALL SELECT date, datetime, cash_type, money, coffee_name FROM index_2;
            - For payment analysis across both tables: SELECT cash_type, COUNT(*) as count FROM (SELECT cash_type FROM index_1 UNION ALL SELECT cash_type FROM index_2) GROUP BY cash_type;
            - For coffee analysis across both tables: SELECT coffee_name, COUNT(*) as count FROM (SELECT coffee_name FROM index_1 UNION ALL SELECT coffee_name FROM index_2) GROUP BY coffee_name;
            
            Please generate a SQL query statement to answer this question. Return only the SQL statement.
            """
            
            # Flash mode: no SQL restrictions, generate complete queries
            if mode == 'Flash':
                prompt += """
            \nFLASH MODE: Generate complete SQL queries without restrictions. Provide full data results as requested.
            """
            
            response = self.client.chat.completions.create(
                model=model_to_use,
                messages=[{"role": "user", "content": prompt}],
                temperature=0.0
            )
            
            sql = response.choices[0].message.content.strip()
            # Clean SQL statement, remove possible markdown formatting
            if sql.startswith('```sql'):
                sql = sql[6:]
            if sql.endswith('```'):
                sql = sql[:-3]
            sql = sql.strip()
            
            print(f"Generated SQL: {sql}")
            return sql
            
        except Exception as e:
            print(f"Error generating SQL: {e}")
            return None

    def generate_sql_multi(self, question, mode=None):
        """Generate one or more SQL statements for a compound question.

        Returns a list of dicts: [{"name": str, "purpose": str, "sql": str}].
        Falls back to a single-item list if only one SQL is appropriate.
        """
        try:
            model_to_use = self.fast_model if (mode == 'Flash' and getattr(self, 'fast_model', None)) else self.model
            prompt = f"""
            You are a SQL expert. Based on the following database schema, generate ONE OR MORE SQL queries to answer the user's question. If the question asks for multiple metrics or views (e.g., counts, breakdowns, top-N lists), split them into separate SQL queries.

            {self.table_info}

            User question: {question}

            Requirements:
            - Use valid SQLite syntax.
            - When combining data from both tables, handle the column differences correctly (index_1 has 'card', index_2 does not).
            - Prefer separate queries for different metrics rather than a single giant query.
            - IMPORTANT: Do NOT add LIMIT clauses unless the user specifically requests a limited number of results (e.g., "top 10", "first 5"). Return ALL data by default for complete CSV exports.
            - Output STRICT JSON with the shape:
              {{
                "queries": [
                  {{"name": "...", "purpose": "...", "sql": "..."}},
                  {{"name": "...", "purpose": "...", "sql": "..."}}
                ]
              }}
            - Do not include any markdown code fences.
            - Keep all strings ASCII-friendly.

            Examples of good separation:
            - Total transactions count across both tables
            - Payment method breakdown (card vs cash)
            - Top-N coffees by count or revenue (only add LIMIT if user specifically requests "top N")
            """

            if mode == 'Flash':
                prompt += "\nFLASH MODE: You may return complete queries without restrictions."

            response = self.client.chat.completions.create(
                model=model_to_use,
                messages=[{"role": "user", "content": prompt}],
                temperature=0.0
            )

            content = response.choices[0].message.content.strip()

            def _extract_sqls_from_text(text):
                import re
                # Try to find multiple SELECT statements terminated by semicolons
                candidates = re.findall(r"\bSELECT\b .*?;", text, re.DOTALL | re.IGNORECASE)
                if not candidates:
                    # Try code blocks
                    blocks = re.findall(r"```(?:sql)?\s*(.*?)```", text, re.DOTALL | re.IGNORECASE)
                    for blk in blocks:
                        if ';' in blk:
                            # Split by semicolons, keep ones with SELECT
                            parts = [p.strip()+';' for p in blk.split(';') if 'select' in p.lower() and p.strip()]
                            candidates.extend(parts)
                result = []
                for i, sql in enumerate(candidates, 1):
                    cleaned = sql.strip()
                    result.append({
                        "name": f"Query {i}",
                        "purpose": "Auto-extracted from non-JSON response",
                        "sql": cleaned
                    })
                return result

            # First try strict JSON parsing
            try:
                payload = json.loads(content)
                queries = payload.get("queries") if isinstance(payload, dict) else None
                if isinstance(queries, list) and len(queries) > 0:
                    # Normalize entries and strip code fences
                    normalized = []
                    for i, q in enumerate(queries, 1):
                        sql = (q.get("sql") or "").strip()
                        if sql.startswith('```sql'):
                            sql = sql[6:]
                        if sql.endswith('```'):
                            sql = sql[:-3]
                        sql = sql.strip()
                        if not sql.endswith(';'):
                            sql = sql + ';'
                        normalized.append({
                            "name": q.get("name") or f"Query {i}",
                            "purpose": q.get("purpose") or "",
                            "sql": sql
                        })
                    if normalized:
                        print(f"Generated SQL list (JSON): {[n['sql'] for n in normalized]}")
                        return normalized
            except Exception:
                # Not JSON; continue to regex extraction
                pass

            # Fallback: extract multiple SQLs from raw text
            extracted = _extract_sqls_from_text(content)
            if extracted:
                print(f"Generated SQL list (fallback): {[n['sql'] for n in extracted]}")
                return extracted

            # Final fallback: use single SQL generator
            single_sql = self.generate_sql(question, mode=mode)
            if single_sql:
                return [{"name": "Query 1", "purpose": "Single query", "sql": single_sql if single_sql.endswith(';') else single_sql + ';'}]
            return []
        except Exception as e:
            print(f"Error generating multi SQL: {e}")
            return []
    
    def execute_sql(self, sql):
        """Execute SQL query"""
        try:
            # Create a new connection for each query to avoid thread safety issues
            with sqlite3.connect(self.db_path) as conn:
                df = pd.read_sql_query(sql, conn)
                print(f"Query result: {len(df)} rows of data")
                return df
        except Exception as e:
            print(f"Error executing SQL: {e}")
            return None
    
    def detect_language(self, text):
        """Detect if text is Simplified Chinese, Traditional Chinese, or English"""
        # 统计中文字符
        chinese_chars = sum(1 for char in text if '\u4e00' <= char <= '\u9fff')
        # 统计所有字符（包括中文字符和英文字母）
        total_chars = len([char for char in text if char.isalpha() or '\u4e00' <= char <= '\u9fff'])
        
        if total_chars == 0:
            return 'english'
        
        chinese_ratio = chinese_chars / total_chars
        
        # 如果中文字符比例低于30%，认为是英文
        if chinese_ratio < 0.3:
            return 'english'
        
        # 检测是否为繁体中文
        # 只使用明确的繁体中文特征字符（在简体中文中不存在或很少使用的字符）
        traditional_chars = set([
            '繁', '體', '語', '問', '題', '資', '料', '數', '據', 
            '統', '計', '銷', '業', '務', '財', '報', '圖', '錶',
            '時', '間', '價', '產', '戶', '員', '營', '費', '潤', 
            '預', '劃', '策', '趨', '勢', '變', '長', '減', '較', 
            '總', '計', '順', '篩', '選', '詢', '搜', '顯', '結', 
            '輸', '匯', '們', '個', '這', '樣', '還', '會', '來',
            '應', '該', '種', '現', '實', '際', '過', '進', '開',
            '關', '於', '為', '與', '從', '對', '將', '經', '處',
            '請', '優', '組', '合', '麼', '別', '詳', '細', '析',
            '些', '何', '化', '品', '線', '類', '別', '項', '目',
            '顯', '示', '勢', '們', '該', '如', '何', '優', '化',
            '產', '品', '組', '合', '這', '些', '數', '據'
        ])
        
        # 统计繁体字符数量
        traditional_count = sum(1 for char in text if char in traditional_chars)
        
        # 降低检测阈值，更容易检测繁体中文
        if chinese_chars > 0 and traditional_count >= 1 and traditional_count / chinese_chars > 0.1:
            return 'traditional_chinese'
        
        # 否则认为是简体中文
        return 'chinese'
    
    def analyze_results(self, question, df):
        """Analyze query results and generate natural language answer"""
        try:
            # Convert DataFrame to string format
            data_summary = df.to_string(max_rows=10)
            
            prompt = f"""
            Based on the following query results, answer the user's question in natural language.
            
            User question: {question}
            
            Query results:
            {data_summary}
            
            Please provide a clear and accurate analysis and answer. If the data volume is large, please summarize the key information.
            """
            
            response = self.client.chat.completions.create(
                model=self.model,
                messages=[{"role": "user", "content": prompt}],
                temperature=0.3
            )
            
            analysis = response.choices[0].message.content.strip()
            return analysis
            
        except Exception as e:
            print(f"Error analyzing results: {e}")
            return "Sorry, unable to analyze query results."
    
    def is_data_related_question(self, question):
        """Detect if a question is data-related or general conversation"""
        try:
            # Use AI to classify the question
            classification_prompt = f"""
            Analyze the following question and determine if it's related to data analysis, database queries, business analytics, or if it's a general conversation question.

            Question: {question}

            Respond with only one word:
            - "DATA" if the question is about data analysis, statistics, business metrics, database queries, sales data, financial data, reports, charts, or any analytical tasks
            - "GENERAL" if the question is about general topics, casual conversation, personal questions, technology discussions, explanations of concepts, or any non-data-analytical topics

            Examples:
            - "How many sales did we have last month?" -> DATA
            - "What's the weather like today?" -> GENERAL
            - "Show me the top 10 customers" -> DATA
            - "How are you doing?" -> GENERAL
            - "Explain machine learning to me" -> GENERAL
            - "What's our revenue trend?" -> DATA
            """
            
            response = self.client.chat.completions.create(
                model=self.model,
                messages=[{"role": "user", "content": classification_prompt}],
                temperature=0.0,
                max_tokens=10
            )
            
            classification = response.choices[0].message.content.strip().upper()
            return classification == "DATA"
            
        except Exception as e:
            print(f"Error classifying question: {e}")
            # Default to treating as data-related to maintain current behavior
            return True
    
    def generate_deepseek_response(self, question):
        """Generate a natural DeepSeek-style response for non-data questions"""
        try:
            # Detect language
            language = self.detect_language(question)
            
            if language == 'chinese':
                system_prompt = """你是DeepSeek，一个由深度求索开发的AI助手。你具有以下特点：
1. 友好、自然、有帮助
2. 能够进行深入的思考和分析
3. 诚实地承认自己的局限性
4. 提供准确、有用的信息
5. 用自然、对话式的语言回应

请以DeepSeek的身份自然地回答用户的问题。"""
            else:
                system_prompt = """You are DeepSeek, an AI assistant developed by DeepSeek. You have the following characteristics:
1. Profeesional, business, and money minded.
2. Capable of deep thinking and analysis
3. Honest about your limitations
4. Provide accurate and useful information
5. Respond in business, conversational language

Please respond naturally as DeepSeek to the user's question."""
            
            response = self.client.chat.completions.create(
                model=self.model,
                messages=[
                    {"role": "system", "content": system_prompt},
                    {"role": "user", "content": question}
                ],
                temperature=0.7,
                max_tokens=1000
            )
            
            return response.choices[0].message.content.strip()
            
        except Exception as e:
            print(f"Error generating DeepSeek response: {e}")
            if language == 'chinese':
                return "抱歉，我现在无法回答这个问题。请稍后再试。"
            else:
                return "Sorry, I'm unable to answer this question right now. Please try again later."
    
    def analyze_data(self, data, question, mode=None):
        """Analyze query results and generate insights"""
        try:
            model_to_use = self.fast_model if (mode == 'Flash' and getattr(self, 'fast_model', None)) else self.model
            # Detect language
            language = self.detect_language(question)
            
            # Convert DataFrame to text format for analysis
            data_summary = f"数据概览：\n- 数据形状：{data.shape}\n- 列名：{list(data.columns)}\n\n前10行数据：\n"
            for i, (idx, row) in enumerate(data.head(10).iterrows()):
                row_data = []
                for col in data.columns:
                    row_data.append(f"{col}: {row[col]}")
                data_summary += f"第{i+1}行 - {', '.join(row_data)}\n"
            
            # Flash mode: provide complete data results without deep analysis
            if mode == 'Flash':
                if language == 'chinese':
                    system_prompt = """你是一个数据查询助手。直接回答用户的数据查询问题，提供完整的查询结果。不要进行深度分析、商业洞察或预测，只需要清晰地呈现数据结果和基本描述。"""
                    user_prompt = f"""
用户问题：{question}

数据信息：
{data_summary}

请直接回答用户的查询问题，提供完整的数据结果。不需要深度分析，只需要清晰地描述查询结果。
"""
                elif language == 'traditional_chinese':
                    system_prompt = """您是一個數據查詢助手。直接回答用戶的數據查詢問題，提供完整的查詢結果。不要進行深度分析、商業洞察或預測，只需要清晰地呈現數據結果和基本描述。"""
                    user_prompt = f"""
用戶問題：{question}

數據信息：
{data_summary}

請直接回答用戶的查詢問題，提供完整的數據結果。不需要深度分析，只需要清晰地描述查詢結果。
"""
                else:
                    system_prompt = """You are a data query assistant. Directly answer the user's data query questions and provide complete query results. Do not perform deep analysis, business insights, or predictions. Just clearly present the data results and basic descriptions."""
                    user_prompt = f"""
User Question: {question}

Data Information:
{data_summary}

Please directly answer the user's query question and provide complete data results. No deep analysis needed, just clearly describe the query results.
"""
            else:
                if language == 'chinese':
                    system_prompt = """你是一位资深的商业数据专家，擅长销售数据分析、人力资源数据分析和商业预测。请用自然、流畅的语言提供专业的数据分析和商业洞察，不要使用固定的格式或模板。"""

                    user_prompt = f"""
用户问题：{question}

数据信息：
{data_summary}

请基于以上数据提供专业的商业分析，用自然的语言表达你的洞察和建议。
"""
                elif language == 'traditional_chinese':
                    system_prompt = """您是一位資深的商業數據專家，擅長銷售數據分析、人力資源數據分析和商業預測。請用自然、流暢的語言提供專業的數據分析和商業洞察，不要使用固定的格式或模板。"""

                    user_prompt = f"""
用戶問題：{question}

數據信息：
{data_summary}

請基於以上數據提供專業的商業分析，用自然的語言表達您的洞察和建議。
"""
                else:
                    system_prompt = """You are a senior business data expert specializing in sales analytics, HR data analysis, and business forecasting. Please provide professional data analysis and business insights using natural, flowing language without fixed formats or templates."""

                    user_prompt = f"""
User Question: {question}

Data Information:
{data_summary}

Please provide professional business analysis based on the above data using natural language to express your insights and recommendations.
"""
            
            response = self.client.chat.completions.create(
                model=model_to_use,
                messages=[
                    {"role": "system", "content": system_prompt},
                    {"role": "user", "content": user_prompt}
                ],
                temperature=0.2
            )
            
            analysis_result = response.choices[0].message.content.strip()
            
            return analysis_result
            
        except Exception as e:
            return f"Error during analysis: {str(e)}"

    def analyze_multi_data(self, datasets, question, mode=None, stream=False):
        """Analyze multiple query result sets together.

        datasets: List[{"name": str, "sql": str, "df": pd.DataFrame}]
        Returns combined analysis text.
        """
        try:
            model_to_use = self.fast_model if (mode == 'Flash' and getattr(self, 'fast_model', None)) else self.model
            language = self.detect_language(question)

            # Build per-dataset summaries
            sections = []
            for idx, item in enumerate(datasets, 1):
                df = item.get("df")
                name = item.get("name") or f"Dataset {idx}"
                sql = item.get("sql") or ""
                if df is None:
                    summary = "No data returned."
                elif getattr(df, 'empty', True):
                    summary = "Query executed successfully, but no rows returned."
                else:
                    sample_rows = 5 if mode == 'Flash' else 10
                    summary = f"数据形状：{df.shape}\n列名：{list(df.columns)}\n\n前{sample_rows}行数据：\n"
                    for i, (idx, row) in enumerate(df.head(sample_rows).iterrows()):
                        row_data = []
                        for col in df.columns:
                            row_data.append(f"{col}: {row[col]}")
                        summary += f"第{i+1}行 - {', '.join(row_data)}\n"
                sections.append({
                    "name": name,
                    "sql": sql,
                    "summary": summary
                })

            # Construct prompts
            if mode == 'Flash':
                if language == 'chinese':
                    system_prompt = "你是一个数据查询助手。请直接将各个查询的结果分别呈现，并在最后给出简短的总体描述。"
                elif language == 'traditional_chinese':
                    system_prompt = "您是一個數據查詢助手。請直接將各個查詢的結果分別呈現，並在最後給出簡短的總體描述。"
                else:
                    system_prompt = "You are a data query assistant. Present each query's results clearly and end with a brief overall description."
            elif mode == 'Charts':
                if language == 'chinese':
                    system_prompt = "你是一个数据分析助手。请简要说明数据的基本情况，包括数据量、主要字段和关键数值。保持回复简洁，不超过3-4句话。"
                elif language == 'traditional_chinese':
                    system_prompt = "您是一個數據分析助手。請簡要說明數據的基本情況，包括數據量、主要字段和關鍵數值。保持回復簡潔，不超過3-4句話。"
                else:
                    system_prompt = "You are a data analysis assistant. Briefly describe the basic data situation, including data volume, main fields, and key values. Keep the response concise, no more than 3-4 sentences."
            elif mode == 'Graphics':
                if language == 'chinese':
                    system_prompt = "你是一个商业视觉设计专家。请分析数据并生成适合制作信息图表、仪表板或可视化报告的洞察。重点关注关键指标、趋势和对比，为图形化展示提供清晰的内容结构。"
                elif language == 'traditional_chinese':
                    system_prompt = "您是一位商業視覺設計專家。請分析數據並生成適合製作信息圖表、儀表板或可視化報告的洞察。重點關注關鍵指標、趨勢和對比，為圖形化展示提供清晰的內容結構。"
                else:
                    system_prompt = "You are a business visual design expert. Analyze data and generate insights suitable for creating infographics, dashboards, or visual reports. Focus on key metrics, trends, and comparisons to provide clear content structure for graphical presentation."
            else:
                if language == 'chinese':
                    system_prompt = "你是一位资深的商业数据专家。请综合分析多个数据集，给出专业洞察与建议。"
                elif language == 'traditional_chinese':
                    system_prompt = "您是一位資深的商業數據專家。請綜合分析多個數據集，給出專業洞察與建議。"
                else:
                    system_prompt = "You are a senior business data expert. Synthesize insights across multiple datasets and provide professional recommendations."

            # Build user prompt with structured sections
            lines = []
            if language == 'chinese':
                lines.append(f"用户问题：{question}")
                lines.append("\n數據集概覽：")
            elif language == 'traditional_chinese':
                lines.append(f"用戶問題：{question}")
                lines.append("\n數據集概覽：")
            else:
                lines.append(f"User Question: {question}")
                lines.append("\nDatasets Overview:")
            for i, s in enumerate(sections, 1):
                lines.append(f"\n---\nDataset {i}: {s['name']}\nSQL:\n{s['sql']}\n\n{s['summary']}")
            user_prompt = "\n".join(lines)

            if not stream:
                response = self.client.chat.completions.create(
                    model=model_to_use,
                    messages=[
                        {"role": "system", "content": system_prompt},
                        {"role": "user", "content": user_prompt}
                    ],
                    temperature=0.2
                )
                analysis_text = response.choices[0].message.content.strip()
                return analysis_text
            else:
                # Streaming mode: yield incremental tokens for SSE
                stream_resp = self.client.chat.completions.create(
                    model=model_to_use,
                    messages=[
                        {"role": "system", "content": system_prompt},
                        {"role": "user", "content": user_prompt}
                    ],
                    temperature=0.2,
                    stream=True
                )
                # The OpenAI python client yields chunks with choices[0].delta.content
                for chunk in stream_resp:
                    try:
                        delta = getattr(chunk.choices[0], 'delta', None)
                        token = getattr(delta, 'content', None) if delta is not None else None
                    except Exception:
                        token = None
                    if not token:
                        # Fallback: sometimes message.content appears at the end
                        try:
                            token = chunk.choices[0].message.content or ''
                        except Exception:
                            token = ''
                    if token:
                        yield token
        except Exception as e:
            return f"Error during multi-dataset analysis: {str(e)}"
    
    def ask_and_analyze(self, question, create_session=True, selected_option=None,
                       has_chart=False, chart_spec=None, chart_data=None, 
                       chart_type=None, chart_error=None, doc_file_url=None,
                       doc_filename=None, excel_file_url=None, excel_filename=None,
                       session_id=None, language=None):
        """Complete analysis process: generate SQL (one or more), execute, and analyze"""
        try:
            print(f"Question: {question}")
            
            # Check if this is a data-related question
            if not self.is_data_related_question(question):
                print("Non-data question detected, generating DeepSeek-style response...")
                deepseek_response = self.generate_deepseek_response(question)
                return None, deepseek_response, None
            
            # Generate multiple SQLs when appropriate
            print("Generating SQL queries...")
            sql_items = self.generate_sql_multi(question, mode=selected_option)
            if not sql_items:
                print("Unable to generate SQL for this question")
                return None, None, None

            # Execute each SQL and collect datasets
            datasets = []
            for item in sql_items:
                sql_stmt = item.get("sql", "").strip().rstrip(';')
                if not sql_stmt:
                    continue
                print(f"Executing: {sql_stmt}")
                df = self.execute_sql(sql_stmt)
                if df is None:
                    print("Execution failed for one of the queries")
                    continue
                datasets.append({"name": item.get("name"), "purpose": item.get("purpose"), "sql": sql_stmt, "df": df})

            if not datasets:
                print("All queries failed or returned no data")
                return None, "No data found for the generated queries.", None

            # Choose first dataset for downstream compatibility
            primary_df = None
            for d in datasets:
                if d["df"] is not None and not d["df"].empty:
                    primary_df = d["df"]
                    break
            if primary_df is None:
                primary_df = datasets[0]["df"]

            # Analyze across datasets
            print("\nAnalyzing multiple datasets...")
            analysis = self.analyze_multi_data(datasets, question, mode=selected_option)
            print(f"\nAnalysis Results (multi):\n{analysis}")

            # Persist some metadata for API consumers
            self.last_sql_list = [d["sql"] for d in datasets]
            # Store lightweight summaries for potential API exposure
            self.last_result_sets = [{
                "name": d.get("name"),
                "purpose": d.get("purpose"),
                "rows": int(d["df"].shape[0]) if hasattr(d["df"], 'shape') else 0,
                "columns": list(d["df"].columns) if hasattr(d["df"], 'columns') else []
            } for d in datasets]
            
            # Save conversation if requested
            if session_id:
                # Add to existing conversation as followup
                context = self.conversation_manager.get_conversation_details(session_id)
                if context:
                    # Join SQLs for storage
                    joined_sql = "\n\n".join([f"-- {i+1}. {item.get('name','Query')}\n{item.get('sql','')}" for i, item in enumerate(sql_items)])
                    
                    # Add followup with complete analysis data
                    followup_data = {
                        "timestamp": datetime.now().isoformat(),
                        "question": question,
                        "sql_query": joined_sql,
                        "query_result": primary_df.to_dict('records') if hasattr(primary_df, 'to_dict') else [],
                        "columns": list(primary_df.columns) if hasattr(primary_df, 'columns') else [],
                        "analysis": analysis,
                        "type": "followup",
                        "selected_option": selected_option
                    }
                    context.conversation_history.append(followup_data)
                    self.conversation_manager.session_manager.update_session(session_id, context)
                    print(f"\nFollowup added to conversation (Session ID: {session_id})")
                else:
                    print(f"Warning: Session {session_id} not found, creating new session")
                    session_id = None
            
            if create_session and not session_id:
                # Create new conversation
                joined_sql = "\n\n".join([f"-- {i+1}. {item.get('name','Query')}\n{item.get('sql','')}" for i, item in enumerate(sql_items)])
                session_id = self.conversation_manager.create_conversation(
                    question=question,
                    sql_query=joined_sql,
                    query_result=primary_df.to_dict('records'),
                    columns=list(primary_df.columns) if hasattr(primary_df, 'columns') else [],
                    analysis=analysis,
                    selected_option=selected_option,
                    has_chart=has_chart,
                    chart_spec=chart_spec,
                    chart_data=chart_data,
                    chart_type=chart_type,
                    chart_error=chart_error,
                    doc_file_url=doc_file_url,
                    doc_filename=doc_filename,
                    excel_file_url=excel_file_url,
                    excel_filename=excel_filename
                )
                print(f"\nConversation saved (Session ID: {session_id})")
            
            return primary_df, analysis, session_id
            
        except Exception as e:
            print(f"Error during analysis: {str(e)}")
            return None, None, None
    
    def ask_followup(self, session_id, followup_question):
        """Ask a follow-up as an independent Q&A using the full analysis pipeline"""
        try:
            # Retrieve language from existing conversation (for consistent output language)
            context = self.conversation_manager.get_conversation_details(session_id)
            if not context:
                print("Conversation not found")
                return None, None

            print(f"Follow-up Question (independent): {followup_question}")

            # Run the same complete analysis workflow used for first questions
            # Passing session_id ensures the result is appended to conversation_history
            query_data, analysis, _ = self.ask_and_analyze(
                followup_question,
                language=context.language,
                session_id=session_id
            )

            # Log results
            print(f"\nFollow-up Analysis (independent):\n{analysis}")
            print(f"\nSaved follow-up entry to conversation {session_id}")

            # Return dataset (if any) and analysis text
            return query_data, analysis

        except Exception as e:
            print(f"Error during follow-up: {str(e)}")
            return None, None
    
    def list_conversations(self):
        """List all conversations"""
        return self.conversation_manager.list_conversations()
    
    def get_conversation_details(self, session_id):
        """Get conversation details"""
        return self.conversation_manager.get_conversation_details(session_id)
    
    def export_session_to_pdf(self, session_id, filename=None):
        """Export conversation session to PDF"""
        try:
            # Get conversation data
            context = self.conversation_manager.get_conversation_details(session_id)
            if not context:
                print("Conversation not found or unable to rebuild query results")
                return None
            
            # Prepare data for export
            export_data = {
                'session_id': session_id,
                'original_question': context.original_question,
                'created_at': context.created_at,
                'language': context.language,
                'conversation_history': []
            }
            
            # Add main conversation (map to generated_sql/query_result/analysis_result)
            try:
                main_data_list = json.loads(context.query_result) if getattr(context, 'query_result', None) else []
            except Exception:
                main_data_list = []
            main_columns = list(main_data_list[0].keys()) if isinstance(main_data_list, list) and len(main_data_list) > 0 and isinstance(main_data_list[0], dict) else []

            main_data = {
                'type': 'main',
                'question': context.original_question,
                'sql_query': getattr(context, 'generated_sql', ''),
                'data': main_data_list,
                'columns': main_columns,
                'analysis': getattr(context, 'analysis_result', ''),
                'timestamp': context.created_at
            }
            export_data['conversation_history'].append(main_data)
            
            # Add followups (only analysis and question are available)
            for followup in context.conversation_history:
                if followup.get('type') == 'followup':
                    followup_data = {
                        'type': 'followup',
                        'question': followup.get('question', ''),
                        'sql_query': followup.get('sql_query', ''),
                        'data': followup.get('query_result', []),
                        'columns': followup.get('columns', []),
                        'analysis': followup.get('analysis') or followup.get('response', ''),
                        'timestamp': followup.get('timestamp', '')
                    }
                    export_data['conversation_history'].append(followup_data)
            
            # Export to PDF
            pdf_path = self.export_manager.export_to_pdf(export_data, filename)
            
            if pdf_path:
                print(f"PDF export successful: {pdf_path}")
                return pdf_path
            else:
                print("PDF export failed")
                return None
                
        except Exception as e:
            print(f"PDF export failed: {str(e)}")
            return None
    
    def export_session_to_csv(self, session_id, filename=None):
        """Export conversation session data to CSV file"""
        try:
            # Get conversation data
            context = self.conversation_manager.get_conversation_details(session_id)
            if not context:
                print("❌ Conversation not found or unable to rebuild query results")
                return None
            
            # Check if there's data to export
            has_data = False
            all_data = []
            
            # Collect main conversation data (parse JSON string)
            try:
                main_data_list = json.loads(context.query_result) if getattr(context, 'query_result', None) else []
            except Exception:
                main_data_list = []
            
            if main_data_list:
                df_main = pd.DataFrame(main_data_list)
                if not df_main.empty:
                    df_main['_conversation_type'] = 'main'
                    df_main['_question'] = context.original_question
                    df_main['_timestamp'] = context.created_at
                    all_data.append(df_main)
                    has_data = True
            
            # Collect followup data (if any query_result is present)
            for followup in context.conversation_history:
                if followup.get('type') == 'followup':
                    follow_data = followup.get('query_result')
                    if follow_data:
                        try:
                            follow_list = json.loads(follow_data) if isinstance(follow_data, str) else follow_data
                        except Exception:
                            follow_list = None
                        if follow_list:
                            df_followup = pd.DataFrame(follow_list)
                            if not df_followup.empty:
                                df_followup['_conversation_type'] = 'followup'
                                df_followup['_question'] = followup.get('question', '')
                                df_followup['_timestamp'] = followup.get('timestamp', '')
                                all_data.append(df_followup)
                                has_data = True
            
            if not has_data:
                print("❌ No data to export")
                return None
            
            # Combine all data
            combined_df = pd.concat(all_data, ignore_index=True, sort=False)
            
            # Export to CSV
            csv_path = self.export_manager.export_to_csv(combined_df, filename)
            
            if csv_path:
                print(f"✅ CSV export successful: {csv_path}")
                return csv_path
            else:
                print("❌ CSV export failed")
                return None
                
        except Exception as e:
            print(f"❌ CSV export failed: {str(e)}")
            return None
    
    def export_current_analysis(self, question, sql_query, data, columns, analysis, export_type="both", filename_prefix=None):
        """Export current analysis results"""
        try:
            # Prepare export data
            export_data = {
                'session_id': 'current_analysis',
                'original_question': question,
                'created_at': datetime.now().isoformat(),
                'language': self.detect_language(question),
                'conversation_history': [{
                    'type': 'main',
                    'question': question,
                    'sql_query': sql_query,
                    'data': data.to_dict('records') if data is not None and not data.empty else [],
                    'columns': columns,
                    'analysis': analysis,
                    'timestamp': datetime.now().isoformat()
                }]
            }
            
            results = {}
            
            if export_type in ["both", "pdf"]:
                # Export PDF
                pdf_filename = f"{filename_prefix}_analysis.pdf" if filename_prefix else None
                pdf_path = self.export_manager.export_to_pdf(export_data, pdf_filename)
                if pdf_path:
                    results["pdf"] = pdf_path
                    print(f"✅ PDF export successful: {pdf_path}")
                else:
                    print("❌ PDF export failed")
            
            if export_type in ["both", "csv"] and data is not None and not data.empty:
                # Export CSV
                csv_filename = f"{filename_prefix}_data.csv" if filename_prefix else None
                csv_path = self.export_manager.export_to_csv(data, csv_filename)
                if csv_path:
                    results["csv"] = csv_path
                    print(f"✅ CSV export successful: {csv_path}")
                else:
                    print("❌ CSV export failed")
            
            return results if results else None
            
        except Exception as e:
            print(f"❌ Export failed: {str(e)}")
            return None

    def export_single_ai_response_to_pdf(self, session_id, analysis_text, filename=None):
        """
        Export only the AI analysis response to PDF without user questions and SQL queries.
        
        Args:
            session_id: Session ID for the conversation
            analysis_text: The AI analysis text to export
            filename: Optional custom filename
            
        Returns:
            str: Path to the generated PDF file or None if failed
        """
        try:
            pdf_path = self.export_manager.export_analysis_only_to_pdf(
                analysis_text,
                session_id,
                filename
            )
            
            if pdf_path:
                print(f"✅ Single response PDF export successful: {pdf_path}")
                return pdf_path
            else:
                print("❌ Single response PDF export failed")
                return None
                
        except Exception as e:
            print(f"❌ Single response PDF export failed: {str(e)}")
            return None
    
    def export_single_response(self, session_id, response_index, export_type="both", filename_prefix=None):
        """Export single AI response"""
        try:
            # Get conversation responses
            responses = self.list_conversation_responses(session_id)
            
            if not responses or response_index >= len(responses):
                print("❌ Conversation not found or invalid response index")
                return None
            
            response = responses[response_index]
            
            # Prepare export data
            export_data = {
                'session_id': f"{session_id}_response_{response_index}",
                'original_question': response['question'],
                'created_at': response['timestamp'],
                'language': self.detect_language(response['question']),
                'conversation_history': [{
                    'type': response['type'],
                    'question': response['question'],
                    'sql_query': response.get('sql_query', ''),
                    'data': response.get('data', []),
                    'columns': response.get('columns', []),
                    'analysis': response['response'],
                    'timestamp': response['timestamp']
                }]
            }
            
            results = {}
            
            if export_type in ["both", "pdf"]:
                # Export PDF
                pdf_filename = f"{filename_prefix}_response_{response_index}.pdf" if filename_prefix else f"response_{session_id}_{response_index}.pdf"
                pdf_path = self.export_manager.export_to_pdf(export_data, pdf_filename)
                if pdf_path:
                    results["pdf"] = pdf_path
            
            if export_type in ["both", "txt"]:
                # Export TXT
                txt_filename = f"{filename_prefix}_response_{response_index}.txt" if filename_prefix else f"response_{session_id}_{response_index}.txt"
                
                # Prepare text content
                text_content = f"""Question: {response['question']}
Time: {response['timestamp'][:19].replace('T', ' ')}
Type: {response['type_display']}

Response:
{response['response']}
"""
                
                if response.get('sql_query'):
                    text_content += f"\n\nSQL Query:\n{response['sql_query']}"
                
                txt_path = self.export_manager.export_to_txt(text_content, txt_filename)
                if txt_path:
                    results["txt"] = txt_path
            
            return results if results else None
            
        except Exception as e:
            print(f"❌ Export failed: {str(e)}")
            return None
    
    def list_conversation_responses(self, session_id):
        """Get list of responses for a conversation"""
        try:
            context = self.conversation_manager.get_conversation_details(session_id)
            if not context:
                return []
            
            responses = []
            
            # Add main response (map to generated_sql/query_result/analysis_result)
            try:
                main_data_list = json.loads(context.query_result) if getattr(context, 'query_result', None) else []
            except Exception:
                main_data_list = []
            main_columns = list(main_data_list[0].keys()) if isinstance(main_data_list, list) and len(main_data_list) > 0 and isinstance(main_data_list[0], dict) else []

            responses.append({
                'index': 0,
                'type': 'main',
                'type_display': 'Main Question',
                'question': context.original_question,
                'response': getattr(context, 'analysis_result', ''),
                'timestamp': context.created_at,
                'sql_query': getattr(context, 'generated_sql', ''),
                'data': main_data_list,
                'columns': main_columns
            })
            
            # Add followup responses
            followup_index = 1
            for item in context.conversation_history:
                if item.get('type') == 'followup':
                    responses.append({
                        'index': followup_index,
                        'type': 'followup',
                        'type_display': f'Followup {followup_index}',
                        'question': item.get('question', ''),
                        'response': item.get('analysis', item.get('response', '')),
                        'timestamp': item.get('timestamp', ''),
                        'sql_query': item.get('sql_query', ''),
                        'data': item.get('query_result', []),
                        'columns': item.get('columns', [])
                    })
                    followup_index += 1
            
            return responses
            
        except Exception as e:
            print(f"❌ Failed to get response list: {str(e)}")
            return []

def show_database_info():
    """Display database information"""
    print("="*80)
    print("Craveva AI Enterprise Business")
    print("="*80)
    print("Database Information:")
    print("   • Database: my_database.db (SQLite)")
    print("   • Tables: index_1, index_2")
    print("   • Sample Data: 3636 main records, 262 cash records")
    print()
    print("Table Structure:")
    print("   index_1: date, datetime, cash_type, card, money, coffee_name")
    print("   index_2: date, datetime, cash_type, money, coffee_name")
    print()
    print("Supported Query Types:")
    print("   • Sales analysis (revenue, trends, top products)")
    print("   • Payment method analysis (card vs cash)")
    print("   • Product analysis (performance, popularity)")
    print("   • Time-based analysis (daily, monthly trends)")
    print("="*80)

def demo_analysis():
    """Demo analysis function"""
    analyzer = SimpleCoffeeAnalyzer()
    
    demo_questions = [
        "What are the top 5 best-selling coffee products?",
        "Show daily sales trends for the last month",
        "What is the total revenue by payment method?"
    ]
    
    for question in demo_questions:
        print(f"\nDemo Question: {question}")
        print("-" * 60)
        analyzer.ask_and_analyze(question, create_session=False)
        print("-" * 60)

def show_conversation_menu(analyzer):
    """Display conversation management menu"""
    conversations = analyzer.list_conversations()
    
    print("\n" + "="*80)
    print("💬 Conversation Management")
    print("="*80)
    
    if not conversations:
        print("📝 No saved conversations")
        print("\nOptions:")
        print("  new    - Create new conversation")
        print("  quit   - Exit program")
        print("="*80)
        
        choice = input("Please select (new/quit): ").strip().lower()
        return choice
    
    print("📚 Conversation List:")
    print("-" * 80)
    for i, conv in enumerate(conversations, 1):
        created_time = conv['created_at'][:19].replace('T', ' ')
        followup_info = f" ({conv['followup_count']} followups)" if conv['followup_count'] > 0 else ""
        print(f"{i:2d}. [{conv['session_id']}] {conv['question_summary']}")
        print(f"    Created: {created_time} | Language: {conv['language']}{followup_info}")
    
    print("-" * 80)
    print("Options:")
    print("  1-{:<2} - Continue conversation (enter number)".format(len(conversations)))
    print("  new    - Create new conversation")
    print("  export - Export conversation data")
    print("  single - Export single AI response")
    print("  quit   - Exit program")
    print("="*80)
    
    choice = input("Please select: ").strip()
    
    if choice.lower() == 'quit':
        return 'quit'
    elif choice.lower() == 'new':
        return 'new'
    elif choice.lower() == 'export':
        return 'export'
    elif choice.lower() == 'single':
        return 'single'
    else:
        try:
            index = int(choice) - 1
            if 0 <= index < len(conversations):
                return conversations[index]['session_id']
            else:
                print("❌ Invalid selection")
                return None
        except ValueError:
            print("❌ Please enter a valid number")
            return None

def show_export_menu(analyzer):
    """Display export menu"""
    conversations = analyzer.list_conversations()
    
    if not conversations:
        print("📝 No saved conversations can be exported")
        return
    
    print("\n📤 Export conversation data")
    print("-" * 80)
    for i, conv in enumerate(conversations, 1):
        created_time = conv['created_at'][:19].replace('T', ' ')
        followup_info = f" ({conv['followup_count']} follow-ups)" if conv['followup_count'] > 0 else ""
        print(f"{i:2d}. [{conv['session_id']}] {conv['question_summary']}")
        print(f"    Created at: {created_time} | Language: {conv['language']}{followup_info}")
    
    print("-" * 80)
    print("Please select a conversation to export (enter number):")
    
    choice = input("Your choice: ").strip()
    
    try:
        index = int(choice) - 1
        if 0 <= index < len(conversations):
            session_id = conversations[index]['session_id']
            
            print("\nExport Options:")
            print("1. Export as PDF report (includes question, SQL, data and analysis)")
            print("2. Export as CSV data file (data only)")
            print("3. Export both PDF and CSV")
            
            export_choice = input("Please select export type (1-3): ").strip()
            
            results = {}
            
            if export_choice == "1":
                # Export PDF
                pdf_path = analyzer.export_session_to_pdf(session_id)
                if pdf_path:
                    results["pdf"] = pdf_path
            elif export_choice == "2":
                # Export CSV
                csv_path = analyzer.export_session_to_csv(session_id)
                if csv_path:
                    results["csv"] = csv_path
            elif export_choice == "3":
                # Export both
                pdf_path = analyzer.export_session_to_pdf(session_id)
                csv_path = analyzer.export_session_to_csv(session_id)
                if pdf_path:
                    results["pdf"] = pdf_path
                if csv_path:
                    results["csv"] = csv_path
            else:
                print("Invalid selection")
                return
            
            # Display export results
            if results:
                print("\nExport successful!")
                for export_type, filepath in results.items():
                    file_info = analyzer.export_manager.get_export_summary(filepath)
                    print(f"📄 {export_type.upper()}: {file_info['filename']} ({file_info['size']})")
                    print(f"   Path: {file_info['filepath']}")
            else:
                print("Export failed")
                
        else:
            print("Invalid selection")
    except ValueError:
        print("Please enter a valid number")

def show_single_response_export_menu(analyzer):
    """Display single response export menu"""
    conversations = analyzer.list_conversations()
    
    if not conversations:
        print("📝 No saved conversations")
        return
    
    print("\n📤 Export single AI response")
    print("-" * 80)
    for i, conv in enumerate(conversations, 1):
        created_time = conv['created_at'][:19].replace('T', ' ')
        followup_info = f" ({conv['followup_count']} follow-ups)" if conv['followup_count'] > 0 else ""
        print(f"{i:2d}. [{conv['session_id']}] {conv['question_summary']}")
        print(f"    Created at: {created_time} | Language: {conv['language']}{followup_info}")
    
    print("-" * 80)
    print("Please select a conversation to view (enter number):")
    
    choice = input("Your choice: ").strip()
    
    try:
        index = int(choice) - 1
        if 0 <= index < len(conversations):
            session_id = conversations[index]['session_id']
            
            # Get all responses for this conversation
            responses = analyzer.list_conversation_responses(session_id)
            
            if not responses:
                print("This conversation has no response records")
                return
            
            print(f"\nAll responses for conversation [{session_id}]:")
            print("-" * 80)
            for response in responses:
                print(f"{response['index']:2d}. [{response['type_display']}] {response['question'][:50]}...")
                print(f"    Time: {response['timestamp'][:19].replace('T', ' ')}")
                print(f"    Response: {response['response'][:80]}...")
                print()
            
            print("-" * 80)
            print("Please select a response to export (enter number):")
            
            response_choice = input("Your choice: ").strip()
            
            try:
                response_index = int(response_choice)
                if 0 <= response_index < len(responses):
                    print("\nExport Options:")
                    print("1. Export as PDF report")
                    print("2. Export as TXT text file")
                    print("3. Export both PDF and TXT")
                    
                    export_choice = input("Please select export type (1-3): ").strip()
                    
                    results = {}
                    
                    if export_choice == "1":
                        # Export PDF
                        results = analyzer.export_single_response(session_id, response_index, "pdf")
                    elif export_choice == "2":
                        # Export TXT
                        results = analyzer.export_single_response(session_id, response_index, "txt")
                    elif export_choice == "3":
                        # Export both
                        results = analyzer.export_single_response(session_id, response_index, "both")
                    else:
                        print("Invalid selection")
                        return
                    
                    # Display export results
                    if results:
                        print("\nExport successful!")
                        for export_type, filepath in results.items():
                            file_info = analyzer.export_manager.get_export_summary(filepath)
                            print(f"📄 {export_type.upper()}: {file_info['filename']} ({file_info['size']})")
                            print(f"   Path: {file_info['filepath']}")
                    else:
                        print("Export failed")
                        
                else:
                    print("Invalid response selection")
            except ValueError:
                print("Please enter a valid number")
                
        else:
            print("Invalid conversation selection")
    except ValueError:
        print("Please enter a valid number")

def interactive_mode():
    """Enhanced interactive mode - supports conversation management and follow-up questions"""
    print("\nEntering Enhanced Interactive Mode")
    print("="*60)
    print("Features:")
    print("• Create new conversation: Each question will be saved as an independent session")
    print("• Follow-up function: Continue asking questions based on existing conversations")
    print("• Session management: View and manage all historical conversations")
    print("="*60)
    
    analyzer = SimpleCoffeeAnalyzer()
    
    while True:
        try:
            # Display conversation management menu
            choice = show_conversation_menu(analyzer)
            
            if choice == 'quit':
                print("Thank you for using Craveva AI Enterprise Business!")
                break
            elif choice == 'new':
                # Create new conversation
                print("\nPlease enter your new question:")
                question = input().strip()
                
                if not question:
                    continue
                
                if question.lower() in ['quit', 'exit', 'q']:
                    print("Thank you for using Craveva AI Enterprise Business!")
                    break
                
                print("="*60)
                data, analysis, session_id = analyzer.ask_and_analyze(question)
                print("="*60)
                
            elif choice == 'export':
                # Export conversation
                show_export_menu(analyzer)
                
            elif choice == 'single':
                # Export single AI response
                show_single_response_export_menu(analyzer)
                
            elif choice is None:
                # Handle invalid selection - continue to show menu again
                continue
                
            elif choice:
                # Follow-up in existing conversation
                session_id = choice
                context = analyzer.get_conversation_details(session_id)
                
                if context:
                    print(f"\nConversation Context (Session {session_id}):")
                    print(f"Original question: {context.original_question}")
                    print(f"Created at: {context.created_at[:19].replace('T', ' ')}")
                    print(f"Follow-up history: {len([h for h in context.conversation_history if h['type'] == 'followup'])} times")
                    
                    print("\nPlease enter your follow-up question:")
                    followup = input().strip()
                    
                    if not followup:
                        continue
                    
                    if followup.lower() in ['quit', 'exit', 'q']:
                        print("Thank you for using Craveva AI Enterprise Business!")
                        break
                    
                    print("="*60)
                    analyzer.ask_followup(session_id, followup)
                    print("="*60)
                else:
                    print("Session does not exist")
            
        except KeyboardInterrupt:
            print("\nThank you for using Craveva AI Enterprise Business!")
            break
        except Exception as e:
            print(f"Error: {str(e)}")

def simple_interactive_mode():
    """Simple interactive mode - single Q&A, no history saved"""
    print("\nEntering Simple Interactive Mode")
    print("="*60)
    print("Mode description: Single Q&A, no conversation history saved")
    print("="*60)
    
    analyzer = SimpleCoffeeAnalyzer()
    
    while True:
        try:
            print("\nPlease enter your question (type 'quit' or 'exit' to quit):")
            question = input().strip()
            
            if question.lower() in ['quit', 'exit', 'q']:
                print("Thank you for using Craveva AI Enterprise Business!")
                break
            
            if not question:
                continue
            
            print("="*60)
            analyzer.ask_and_analyze(question, create_session=False)
            print("="*60)
            
        except KeyboardInterrupt:
            print("\nThank you for using Craveva AI Enterprise Business!")
            break
        except Exception as e:
            print(f"Error: {str(e)}")

if __name__ == "__main__":
    # 显示数据库信息
    show_database_info()
    
    # 直接进入交互模式
    interactive_mode()