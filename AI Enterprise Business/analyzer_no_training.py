#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Modified E3MySQLAnalyzer without Vanna training initialization
"""

import os
import sys
from dotenv import load_dotenv
from openai import OpenAI
from conversation_manager import ConversationManager
from export_manager import ExportManager

# Load environment variables
load_dotenv()

class E3MySQLAnalyzerNoTraining:
    """E3 MySQL数据库分析器 - 无Vanna训练版本"""
    
    def __init__(self):
        """Initialize analyzer without Vanna training"""
        print("🔄 Initializing E3MySQLAnalyzer (no training)...")
        
        # Company filtering optional; disabled unless explicitly enabled via env
        _cid_env = os.getenv('COMPANY_ID', '').strip()
        try:
            self.current_company_id = int(_cid_env) if _cid_env else None
        except Exception:
            self.current_company_id = None
        
        company_filter_env = os.getenv('COMPANY_FILTER_ENABLED', '').strip().lower()
        self.company_filter_enabled = (
            company_filter_env in ('1', 'true', 'yes', 'on')
            and isinstance(self.current_company_id, int)
            and self.current_company_id > 0
        )
        
        # MySQL数据库配置
        self.mysql_config = {
            'host': os.getenv('MYSQL_HOST', 'localhost'),
            'port': int(os.getenv('MYSQL_PORT', 3306)),
            'database': os.getenv('MYSQL_DATABASE', 'esp92032_e3'),
            'user': os.getenv('MYSQL_USER', 'root'),
            'password': os.getenv('MYSQL_PASSWORD', ''),
            'charset': 'utf8mb4'
        }
        
        # Get API provider from environment
        api_provider = os.getenv('API_PROVIDER', 'openai').lower()
        
        # Create OpenAI client based on provider
        if api_provider == 'deepseek':
            # For DeepSeek, we'll use OpenAI-compatible client
            self.client = OpenAI(
                api_key=os.getenv('DEEPSEEK_API_KEY'),
                base_url=os.getenv('DEEPSEEK_BASE_URL', 'https://api.deepseek.com')
            )
            self.model = os.getenv('DEEPSEEK_MODEL', 'deepseek-chat')
            self.fast_model = os.getenv('DEEPSEEK_FAST_MODEL')
        else:
            # Configure OpenAI (default)
            self.client = OpenAI(api_key=os.getenv('OPENAI_API_KEY'))
            self.model = os.getenv('OPENAI_MODEL', 'gpt-4')
            self.fast_model = os.getenv('OPENAI_FAST_MODEL')
        
        print(f"✅ Using {api_provider} with model: {self.model}")
        
        # 数据库连接
        self.connection = None
        self.connect_to_database()
        # Auto-detect company_id only when filter explicitly enabled
        if self.company_filter_enabled:
            if not isinstance(self.current_company_id, int) or self.current_company_id <= 0:
                if not self._auto_detect_company_id():
                    self.company_filter_enabled = False
                    self.current_company_id = None
        
        # Initialize conversation manager
        self.conversation_manager = ConversationManager(self.client, self.model, mysql_config=self.mysql_config)
        
        # Initialize export manager
        self.export_manager = ExportManager()
        
        # Cache for schema info
        self._schema_cache = None
        
        # Initialize attributes that multi_sql_flow expects
        self.last_sql_list = []
        self.last_result_sets = []
        
        print("✅ E3MySQLAnalyzer initialized successfully (no training)")
    
    def connect_to_database(self) -> bool:
        """连接到MySQL数据库"""
        try:
            import mysql.connector
            from mysql.connector import Error
            
            self.connection = mysql.connector.connect(**self.mysql_config)
            
            if self.connection.is_connected():
                db_info = self.connection.get_server_info()
                print(f"✅ 成功连接到MySQL数据库")
                print(f"   服务器版本: {db_info}")
                print(f"   数据库: {self.mysql_config['database']}")
                return True
        except Exception as e:
            print(f"❌ 连接MySQL数据库时出错: {e}")
            print(f"   请检查数据库配置: {self.mysql_config}")
            return False
    
    def disconnect_from_database(self):
        """断开数据库连接"""
        if self.connection and self.connection.is_connected():
            self.connection.close()
            print("已断开MySQL数据库连接")

    def _auto_detect_company_id(self) -> bool:
        """Detect a likely company_id by scanning common tables and choose the most populous one."""
        try:
            if not self.connection or not self.connection.is_connected():
                print("[CompanyID] Skipping auto-detect: not connected")
                return False

            cursor = self.connection.cursor()
            candidate_tables = [
                'invoices', 'employee_details', 'client_details', 'attendances',
                'projects', 'purchase_orders', 'expenses'
            ]
            counts = {}
            for t in candidate_tables:
                try:
                    cursor.execute(f"SHOW COLUMNS FROM `{t}` LIKE 'company_id'")
                    has_col = cursor.fetchone()
                    if not has_col:
                        continue
                    cursor.execute(
                        f"SELECT `company_id`, COUNT(*) as cnt FROM `{t}` GROUP BY `company_id` ORDER BY cnt DESC LIMIT 1"
                    )
                    row = cursor.fetchone()
                    if row and len(row) >= 2:
                        cid, cnt = row[0], row[1]
                        if cid is not None:
                            counts[int(cid)] = max(counts.get(int(cid), 0), int(cnt))
                except Exception:
                    # Ignore per-table errors
                    continue

            cursor.close()

            if counts:
                best_cid = max(counts.items(), key=lambda x: x[1])[0]
                self.current_company_id = int(best_cid)
                print(f"[CompanyID] Auto-detected company_id = {self.current_company_id}")
                return True

            print("[CompanyID] Auto-detect found no dominant company; disabling company filter")
            self.current_company_id = None
            return False
        except Exception as e:
            print(f"[CompanyID] Auto-detect error: {e}")
            self.current_company_id = None
            return False
    
    def run_sql(self, sql: str, **kwargs):
        """Execute SQL query and return results as DataFrame"""
        import pandas as pd
        import mysql.connector
        from mysql.connector import Error
        
        if not self.connection or not self.connection.is_connected():
            raise Exception("数据库未连接")
        
        try:
            # Ensure company_id filter is present when applicable
            sql = self._ensure_company_filter(sql)
            
            cursor = self.connection.cursor(dictionary=True)
            cursor.execute(sql)
            
            # Fetch results
            results = cursor.fetchall()
            cursor.close()
            
            # Convert to DataFrame
            df = pd.DataFrame(results)
            
            # Log the query execution
            print(f"✅ SQL执行成功，返回 {len(df)} 行数据")
            
            return df
            
        except Error as e:
            error_msg = f"SQL执行错误: {e}\nSQL: {sql}"
            print(f"❌ {error_msg}")
            raise Exception(error_msg)
    
    def _add_company_filter(self, sql: str) -> str:
        """Add company_id filter to SQL if not present"""
        # This is a simplified version - you may need to implement the full logic
        if not self.company_filter_enabled or self.current_company_id is None:
            return sql
        if 'company_id' not in sql.lower() and 'where' in sql.lower():
            # Simple insertion - this is basic and may need refinement
            sql = sql.replace('WHERE', f'WHERE company_id = {self.current_company_id} AND', 1)
        elif 'company_id' not in sql.lower() and 'from' in sql.lower():
            # Add WHERE clause if none exists
            import re
            # Find the table name and add WHERE clause
            match = re.search(r'FROM\s+`?(\w+)`?', sql, re.IGNORECASE)
            if match:
                table_name = match.group(1)
                # Check if this table likely has company_id (simplified check)
                if table_name not in ['users', 'companies']:  # Add more exceptions as needed
                    sql = sql + f' WHERE company_id = {self.current_company_id}'
        
        return sql
    
    def get_schema_info(self) -> str:
        """Get basic schema information"""
        if self._schema_cache:
            return self._schema_cache
        
        try:
            if not self.connection or not self.connection.is_connected():
                return "Database not connected"
            
            cursor = self.connection.cursor()
            cursor.execute("SHOW TABLES")
            tables = [table[0] for table in cursor.fetchall()]
            
            schema_info = f"Database: {self.mysql_config['database']}\n"
            schema_info += f"Tables ({len(tables)}): {', '.join(tables[:10])}"
            if len(tables) > 10:
                schema_info += f" ... and {len(tables) - 10} more"
            
            cursor.close()
            self._schema_cache = schema_info
            return schema_info
            
        except Exception as e:
            return f"Schema info error: {e}"
    
    def _build_memory_summary(self, session_id: str) -> str:
        """Build memory summary for conversation context"""
        if not session_id:
            return ""
        
        try:
            # Get recent conversation history
            conversations = self.conversation_manager.get_conversation_history(session_id, limit=5)
            if not conversations:
                return ""
            
            summary = "Recent conversation context:\n"
            for conv in conversations[-3:]:  # Last 3 conversations
                summary += f"Q: {conv.get('question', '')[:100]}...\n"
                summary += f"A: {conv.get('analysis', '')[:100]}...\n\n"
            
            return summary
            
        except Exception as e:
            print(f"Error building memory summary: {e}")
            return ""
    
    def generate_sql(self, question, mode=None, session_id=None):
        """Generate SQL query based on question"""
        
        # 选择模型（不区分大小写处理 Flash 模式）
        mode_lower = (mode or '').lower()
        current_model = self.fast_model if mode_lower == 'flash' and self.fast_model else self.model

        try:
            print("🤖 使用LLM生成SQL")
            
            # 构建增强的问题，包含必要的SQL约束
            rules = []
            if self.company_filter_enabled and self.current_company_id is not None:
                rules.append(f"所有查询必须限制在company_id = {self.current_company_id}的数据范围内")
                rules.append("如果查询的表包含company_id字段，必须添加WHERE条件过滤")
            else:
                rules.append("如果查询需要区分公司，请在SQL中显式处理company_id字段")
            rules.extend([
                "使用反引号包围表名和列名",
                "对于聚合函数，使用COALESCE处理NULL值"
            ])
            rules_text = "\n- ".join(rules)
            enhanced_question = f"""
{question}

重要要求：
- {rules_text}
"""
            
            # 注入会话记忆
            if session_id:
                _mem = self._build_memory_summary(session_id)
                if _mem:
                    enhanced_question = f"会话记忆（供参考）：\n{_mem}\n\n{enhanced_question}"
            
            # 使用LLM生成SQL
            sql = self._generate_sql_with_llm(enhanced_question, current_model)
            
            if not sql:
                raise Exception("LLM未能生成SQL")
            
            # 后处理：确保SQL包含company_id过滤条件
            sql = self._ensure_company_filter(sql)
            # 后处理：根据问题自动注入日期过滤
            sql = self._ensure_date_filter(sql, question)
            
            print(f"✅ LLM生成SQL成功: {sql[:100]}...")
            return sql
        
        except Exception as e:
            print(f"❌ LLM生成SQL失败: {e}")
            return None
    
    def _generate_sql_with_llm(self, question, model):
        """Generate SQL using LLM"""
        schema_info = self.get_schema_info()
        
        system_prompt = f"""你是一个专业的SQL查询生成助手。基于以下数据库结构，为用户问题生成准确的SQL查询。

数据库结构:
{schema_info}

重要规则:
1. 只返回SQL查询语句，不要包含任何解释
2. 使用反引号包围表名和列名
3. 确保SQL语法正确
4. 对于中文字段值，使用UTF-8编码
5. 返回所有相关数据，除非用户明确要求限制数量（如"前10个"、"最多5条"等）
6. 使用适当的WHERE条件过滤数据
7. 对于日期查询，使用正确的日期格式
8. **重要：所有查询必须限制在company_id = {self.current_company_id}的数据范围内**
   - 如果查询的表包含company_id字段，必须添加WHERE条件：company_id = {self.current_company_id}
   - 如果查询涉及多个表，确保所有相关表都添加company_id过滤条件"""

        try:
            response = self.client.chat.completions.create(
                model=model,
                messages=[
                    {"role": "system", "content": system_prompt},
                    {"role": "user", "content": question}
                ],
                temperature=0.1,
                max_tokens=1000
            )
            
            sql = response.choices[0].message.content.strip()
            
            # Clean up the SQL
            sql = sql.replace('```sql', '').replace('```', '').strip()
            
            return sql
            
        except Exception as e:
            print(f"LLM API调用失败: {e}")
            return None
    
    def _ensure_company_filter(self, sql: str) -> str:
        """Ensure company_id filter is present in SQL"""
        if not sql or 'company_id' in sql.lower():
            return sql
        if not self.company_filter_enabled or self.current_company_id is None:
            return sql
        
        # Simple implementation - add company_id filter
        sql_lower = sql.lower()
        if 'where' in sql_lower:
            # Insert company_id condition after WHERE
            sql = sql.replace('WHERE', f'WHERE company_id = {self.current_company_id} AND', 1)
            sql = sql.replace('where', f'where company_id = {self.current_company_id} AND', 1)
        else:
            # Add WHERE clause
            if sql.rstrip().endswith(';'):
                sql = sql.rstrip()[:-1] + f' WHERE company_id = {self.current_company_id};'
            else:
                sql = sql + f' WHERE company_id = {self.current_company_id}'
        
        return sql
    
    def _ensure_date_filter(self, sql: str, question: str) -> str:
        """Add date filter if needed based on question context"""
        # Simple implementation - just return the SQL as is for now
        return sql
    
    def execute_sql(self, sql: str):
        """Execute SQL and return DataFrame"""
        return self.run_sql(sql)
    
    def analyze_multi_data(self, datasets, question, mode=None, session_id=None, stream=False):
        """分析多个数据集并提供综合洞察"""
        if stream:
            return self._analyze_multi_data_stream(datasets, question, mode, session_id)
        else:
            return self._analyze_multi_data_sync(datasets, question, mode, session_id)
    
    def _analyze_multi_data_sync(self, datasets, question, mode=None, session_id=None):
        """同步分析多个数据集"""
        try:
            # 选择模型
            mode_lower = (mode or '').lower()
            current_model = self.fast_model if mode_lower == 'flash' and self.fast_model else self.model
            
            # 构建分析提示
            analysis_prompt = f"""基于以下多个数据集，请对用户问题进行综合分析：

用户问题：{question}

数据集信息："""
            
            for i, dataset in enumerate(datasets, 1):
                df = dataset.get('df')
                purpose = dataset.get('purpose', '')
                sql = dataset.get('sql', '')
                name = dataset.get('name', f'数据集{i}')
                
                if df is not None and not getattr(df, 'empty', True):
                    row_count = len(df)
                    columns = ', '.join(df.columns.tolist()) if hasattr(df, 'columns') else 'N/A'
                    
                    # 获取前几行数据作为示例
                    head_data = df.head(3).to_string(index=False) if hasattr(df, 'head') else 'No data'
                    
                    analysis_prompt += f"""

{i}. {name}
   目的：{purpose}
   SQL查询：{sql}
   数据行数：{row_count}
   字段：{columns}
   数据示例：
{head_data}
"""
                else:
                    analysis_prompt += f"""

{i}. {name}
   目的：{purpose}
   SQL查询：{sql}
   结果：无数据
"""
            
            analysis_prompt += """

请基于以上数据集提供综合分析，包括：
1. 数据概览
2. 关键发现
3. 趋势分析
4. 建议和结论

请用中文回答。"""
            
            # 调用LLM进行分析
            response = self.client.chat.completions.create(
                model=current_model,
                messages=[
                    {"role": "system", "content": "你是一个专业的数据分析师，擅长从多个数据集中提取洞察和趋势。"},
                    {"role": "user", "content": analysis_prompt}
                ],
                temperature=0.3,
                max_tokens=2000
            )
            
            analysis = response.choices[0].message.content.strip()
            return analysis
            
        except Exception as e:
            print(f"多数据集分析失败: {e}")
            return f"分析过程中出现错误: {e}"
    
    def _analyze_multi_data_stream(self, datasets, question, mode=None, session_id=None):
        """流式分析多个数据集"""
        try:
            # 选择模型
            mode_lower = (mode or '').lower()
            current_model = self.fast_model if mode_lower == 'flash' and self.fast_model else self.model
            
            # 构建分析提示
            analysis_prompt = f"""基于以下多个数据集，请对用户问题进行综合分析：

用户问题：{question}

数据集信息："""
            
            for i, dataset in enumerate(datasets, 1):
                df = dataset.get('df')
                purpose = dataset.get('purpose', '')
                sql = dataset.get('sql', '')
                name = dataset.get('name', f'数据集{i}')
                
                if df is not None and not getattr(df, 'empty', True):
                    row_count = len(df)
                    columns = ', '.join(df.columns.tolist()) if hasattr(df, 'columns') else 'N/A'
                    
                    # 获取前几行数据作为示例
                    head_data = df.head(3).to_string(index=False) if hasattr(df, 'head') else 'No data'
                    
                    analysis_prompt += f"""

{i}. {name}
   目的：{purpose}
   SQL查询：{sql}
   数据行数：{row_count}
   字段：{columns}
   数据示例：
{head_data}
"""
                else:
                    analysis_prompt += f"""

{i}. {name}
   目的：{purpose}
   SQL查询：{sql}
   结果：无数据
"""
            
            analysis_prompt += """

请基于以上数据集提供综合分析，包括：
1. 数据概览
2. 关键发现
3. 趋势分析
4. 建议和结论

请用中文回答。"""
            
            # 调用LLM进行流式分析
            response = self.client.chat.completions.create(
                model=current_model,
                messages=[
                    {"role": "system", "content": "你是一个专业的数据分析师，擅长从多个数据集中提取洞察和趋势。"},
                    {"role": "user", "content": analysis_prompt}
                ],
                temperature=0.3,
                max_tokens=2000,
                stream=True
            )
            
            for chunk in response:
                if chunk.choices[0].delta.content:
                    yield chunk.choices[0].delta.content
            
        except Exception as e:
            print(f"多数据集分析失败: {e}")
            yield f"分析过程中出现错误: {e}"