#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Craveva AI Enterprise Business - MySQL版本
支持连接到MySQL数据库进行AI数据分析
使用Vanna框架进行自然语言到SQL的转换
"""

import os
import sys
import re
import logging
from logging.handlers import RotatingFileHandler
import mysql.connector
from mysql.connector import Error
import pandas as pd
import numpy as np
import json
import traceback
from datetime import datetime
from dotenv import load_dotenv
from openai import OpenAI
from conversation_manager import ConversationManager
from export_manager import ExportManager, DataFormatter

# Vanna framework imports
from src.vanna_core import VannaDefault, OpenAI_Chat, ChromaDB_VectorStore
from src.vanna_core.base.base import VannaBase

# Load environment variables
load_dotenv()

class E3MySQLAnalyzer(VannaDefault):
    """E3 MySQL数据库分析器"""
    
    def __init__(self):
        """Initialize analyzer with Vanna framework"""
        print("🔍 Starting E3MySQLAnalyzer initialization...")
        
        # Company filtering is optional; disabled unless explicitly enabled via env
        _cid_env = os.getenv('COMPANY_ID', '').strip()
        try:
            self.current_company_id = int(_cid_env) if _cid_env else None
        except Exception:
            self.current_company_id = None

        if not isinstance(self.current_company_id, int) or self.current_company_id <= 0:
            try:
                self.current_company_id = int(os.getenv('DEFAULT_COMPANY_ID', '5'))
            except Exception:
                self.current_company_id = 5

        self.company_filter_enabled = True
        print(f"📋 Company ID: {self.current_company_id}, Filter enabled: {self.company_filter_enabled}")
        
        # MySQL数据库配置
        self.mysql_config = {
            'host': os.getenv('MYSQL_HOST', 'localhost'),
            'port': int(os.getenv('MYSQL_PORT', 3306)),
            'database': os.getenv('MYSQL_DATABASE', 'esp92032_REDACTED'),
            'user': os.getenv('MYSQL_USER', 'root'),
            'password': os.getenv('MYSQL_PASSWORD', ''),
            'charset': 'utf8mb4'
        }
        print(f"🔧 MySQL config: host={self.mysql_config['host']}, port={self.mysql_config['port']}, database={self.mysql_config['database']}, user={self.mysql_config['user']}")
        
        # Initialize connection pool
        self.connection_pool = None
        self.connection_pool_config = {
            'pool_name': 'e3_mysql_pool',
            'pool_size': 10,  # Maximum number of connections in the pool
            'pool_reset_session': True,
            'autocommit': True,
            'connect_timeout': 10,
            'buffered': True,
        }
        print(f"🔧 Connection pool config: pool_size={self.connection_pool_config['pool_size']}, pool_name={self.connection_pool_config['pool_name']}")
        
        # Get API provider from environment
        api_provider = os.getenv('API_PROVIDER', 'openai').lower()
        print(f"🤖 API provider: {api_provider}")
        
        # Configure Vanna with appropriate LLM
        vanna_config = {
            'dialect': 'MySQL',
            'language': 'Chinese',
            'max_tokens': 14000,
        }
        
        # Create OpenAI client based on provider
        if api_provider == 'deepseek':
            # For DeepSeek, we'll use OpenAI-compatible client
            from openai import OpenAI
            openai_client = OpenAI(
                api_key=os.getenv('DEEPSEEK_API_KEY'),
                base_url=os.getenv('DEEPSEEK_BASE_URL', 'https://api.deepseek.com')
            )
            vanna_config.update({
                'model': os.getenv('DEEPSEEK_MODEL', 'deepseek-chat'),
            })
            self.model = os.getenv('DEEPSEEK_MODEL', 'deepseek-chat')
            self.fast_model = os.getenv('DEEPSEEK_FAST_MODEL')
        else:
            # Configure OpenAI (default) for Vanna
            from openai import OpenAI
            openai_client = OpenAI(api_key=os.getenv('OPENAI_API_KEY'))
            vanna_config.update({
                'model': os.getenv('OPENAI_MODEL', 'gpt-4'),
            })
            self.model = os.getenv('OPENAI_MODEL', 'gpt-4')
            self.fast_model = os.getenv('OPENAI_FAST_MODEL')
        
        print(f"🎯 Model: {self.model}, Fast model: {self.fast_model}")
        
        # Initialize Vanna base class components separately
        from src.vanna_core.chromadb import ChromaDB_VectorStore
        from src.vanna_core.openai import OpenAI_Chat
        
        print("🧠 Initializing ChromaDB vector store...")
        # Initialize ChromaDB vector store
        ChromaDB_VectorStore.__init__(self, config=vanna_config)
        
        print("💬 Initializing OpenAI chat...")
        # Initialize OpenAI chat with client
        OpenAI_Chat.__init__(self, client=openai_client, config=vanna_config)
        
        print("🔗 Connecting to database...")
        # 数据库连接
        self.connection = None
        self.connect_to_database()
        print(f"✅ Database connection result: {self.connection is not None}")
        # Auto-detect company_id only when filter explicitly enabled
        if self.company_filter_enabled:
            print("🔍 Auto-detecting company ID...")
            if not isinstance(self.current_company_id, int) or self.current_company_id <= 0:
                if not self._auto_detect_company_id():
                    # Disable filter if detection fails
                    self.company_filter_enabled = False
                    self.current_company_id = None
                    print("⚠️  Company ID auto-detection failed, disabling filter")
                else:
                    print(f"✅ Company ID auto-detected: {self.current_company_id}")
            else:
                print(f"✅ Using predefined company ID: {self.current_company_id}")
        
        # Use the same OpenAI client for conversation manager
        self.client = openai_client
        
        print("💬 Initializing conversation manager...")
        # Initialize conversation manager
        self.conversation_manager = ConversationManager(self.client, self.model, mysql_config=self.mysql_config)
        
        print("📊 Initializing export manager...")
        # Initialize export manager
        self.export_manager = ExportManager()
        
        # Cache for schema info
        self._schema_cache = None
        self._schema_summary_cache = None
        self._table_catalog_cache = None
        self._table_domain_reference_cache = None
        self._tables_with_company_id_cache = None

        # Domain heuristics used for dynamic schema grouping
        self._domain_prefix_catalog = self._build_domain_prefix_catalog()
        self._schema_guardrails = []
        self._update_schema_guardrails()

        # Initialize main logger
        self.logger = logging.getLogger(__name__)
        if not self.logger.handlers:
            self.logger.setLevel(logging.INFO)
            # Create logs directory if it doesn't exist
            log_dir = os.path.join(os.getcwd(), 'logs')
            if not os.path.exists(log_dir):
                os.makedirs(log_dir, exist_ok=True)
            # Add file handler
            log_file = os.path.join(log_dir, 'app.log')
            handler = RotatingFileHandler(
                log_file,
                maxBytes=10 * 1024 * 1024,
                backupCount=5,
                encoding='utf-8'
            )
            formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')
            handler.setFormatter(formatter)
            self.logger.addHandler(handler)
        
        # SQL debugging / instrumentation
        self.sql_debug_enabled = os.getenv('SQL_DEBUG_ENABLED', '0').strip().lower() in ('1', 'true', 'yes')
        self.sql_debug_log_path = os.getenv('SQL_DEBUG_LOG', os.path.join(os.getcwd(), 'logs', 'sql_debug.log'))
        self.sql_debug_logger = None
        if self.sql_debug_enabled:
            self._init_sql_debug_logger()
        
        # Initialize Vanna training data
        print("📚 Initializing Vanna training data...")
        self._initialize_vanna_training()

        print("✅ E3MySQLAnalyzer initialization completed successfully!")

        # Default date column map per table for date filtering
        # Fallback to created_at if unknown
        self._date_column_map = {
            # Finance
            'invoices': 'issue_date',
            'payments': 'paid_on',
            'invoice_payment_details': 'payment_date',
            'credit_notes': 'created_at',
            'estimates': 'created_at',
            'proposals': 'created_at',
            'global_invoices': 'created_at',
            'global_subscriptions': 'created_at',
            'expenses': 'purchase_date',
            'expenses_recurring': 'created_at',

            # Purchase
            'purchase_vendors': 'created_at',
            'purchase_vendor_payments': 'payment_date',
            'purchase_vendor_credits': 'credit_date',
            'purchase_orders': 'purchase_date',
            'purchase_bills': 'bill_date',
            'purchase_items': 'created_at',
            'purchase_stock_adjustments': 'date',

            # HR/Attendance
            'attendances': 'clock_in_time',
            'employee_details': 'created_at',
            'employee_docs': 'created_at',
            'employee_monthly_salaries': 'date',
            'employee_payroll_cycles': 'created_at',
            'salary_slips': 'created_at',

            # Projects/Tasks
            'projects': 'created_at',
            'project_time_logs': 'start_time',
            'tasks': 'created_at',

            # CRM
            'client_details': 'created_at',
            'deals': 'close_date',
            'leads': 'created_at',

            # Assets
            'assets': 'created_at',

            # Misc
            'notices': 'created_at',
            'notice_views': 'created_at',
            'events': 'start_date',
            'event_attendees': 'created_at',
        }
    
    def _init_sql_debug_logger(self):
        """Initialize rotating logger for SQL debugging."""
        try:
            logger = logging.getLogger('sql_debug')
            if not logger.handlers:
                logger.setLevel(logging.INFO)
                logger.propagate = False
                log_dir = os.path.dirname(self.sql_debug_log_path)
                if log_dir and not os.path.exists(log_dir):
                    os.makedirs(log_dir, exist_ok=True)
                handler = RotatingFileHandler(
                    self.sql_debug_log_path,
                    maxBytes=5 * 1024 * 1024,
                    backupCount=3,
                    encoding='utf-8'
                )
                formatter = logging.Formatter('%(asctime)s %(levelname)s %(message)s')
                handler.setFormatter(formatter)
                logger.addHandler(handler)
            self.sql_debug_logger = logger
            self._debug_log('logger_initialized', log_path=self.sql_debug_log_path)
        except Exception as exc:
            print(f"[SQL Debug] Failed to initialize logger: {exc}")
            self.sql_debug_enabled = False
            self.sql_debug_logger = None

    def _debug_log(self, stage: str, **payload):
        """Write structured debug payload to logger when enabled."""
        if not self.sql_debug_enabled or not self.sql_debug_logger:
            return
        entry = {
            'stage': stage,
            'company_filter_enabled': self.company_filter_enabled,
            'current_company_id': self.current_company_id,
            'payload': payload
        }
        try:
            self.sql_debug_logger.info(json.dumps(entry, ensure_ascii=False))
        except Exception as exc:
            print(f"[SQL Debug] Failed to write log: {exc}")

    def log_debug_event(self, stage: str, **payload):
        """Public helper for other modules (e.g., web_api) to log SQL debug info."""
        self._debug_log(stage, **payload)

    def connect_to_database(self) -> bool:
        """
        连接到MySQL数据库，使用连接池
        
        Returns:
            bool: 连接是否成功
        """
        try:
            # Create connection pool configuration
            pool_config = self.mysql_config.copy()
            pool_config.update(self.connection_pool_config)
            
            # Create connection pool
            self.connection_pool = mysql.connector.pooling.MySQLConnectionPool(**pool_config)
            
            # Get a connection from the pool to test
            test_conn = self.connection_pool.get_connection()
            if test_conn.is_connected():
                db_info = test_conn.get_server_info()
                print(f"✅ 成功创建MySQL连接池")
                print(f"   服务器版本: {db_info}")
                print(f"   数据库: {self.mysql_config['database']}")
                print(f"   连接池大小: {self.connection_pool_config['pool_size']}")
                test_conn.close()
                return True
        except Error as e:
            print(f"❌ 创建MySQL连接池时出错: {e}")
            print(f"   请检查数据库配置: {self.mysql_config}")
            return False
    
    def _ensure_connection(self) -> bool:
        """
        确保可以从连接池获取有效连接
        
        Returns:
            bool: 连接是否有效
        """
        try:
            if self.connection_pool:
                # Test connection pool by getting a connection
                test_conn = self.connection_pool.get_connection()
                if test_conn.is_connected():
                    cursor = test_conn.cursor()
                    cursor.execute("SELECT 1")
                    cursor.fetchone()
                    cursor.close()
                    test_conn.close()
                    return True
                else:
                    test_conn.close()
            
            # Connection pool is not available, recreate it
            print("⚠️ 连接池不可用，正在重新创建...")
            return self.connect_to_database()
        except Exception as e:
            print(f"⚠️ 检查连接池时出错: {e}")
            return self.connect_to_database()
    
    def disconnect_from_database(self):
        """断开数据库连接池"""
        if self.connection_pool:
            # MySQL connection pool doesn't have a close method, but we can reset it
            self.connection_pool = None
            print("已断开MySQL数据库连接池")

    def _auto_detect_company_id(self) -> bool:
        """Detect a likely company_id by scanning common tables and choosing the most populous one."""
        try:
            if not self._ensure_connection():
                print("[CompanyID] Skipping auto-detect: not connected")
                return False

            cursor = None
            conn = None
            try:
                # Get connection from pool
                conn = self.connection_pool.get_connection()
                if not conn:
                    print("[CompanyID] Skipping auto-detect: no connection available")
                    return False
                
                cursor = conn.cursor()
            # NOTE: This list is ONLY for auto-detection of company_id
            # The system can access ALL tables via get_table_list() - this is NOT a restriction
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

            # No signal -> leave filter disabled
            print("[CompanyID] Auto-detect found no dominant company; disabling company filter")
            self.current_company_id = None
            return False
        except Exception as e:
            print(f"[CompanyID] Auto-detect error: {e}")
            # Disable filter on error
            self.current_company_id = None
            return False
        finally:
            # Clean up cursor and connection
            if cursor:
                try:
                    cursor.close()
                except Exception:
                    pass
            if conn:
                try:
                    conn.close()  # Return to pool
                except Exception:
                    pass
    
    def run_sql(self, sql: str, **kwargs) -> pd.DataFrame:
        """
        Execute SQL query and return results as DataFrame
        Required by Vanna framework
        
        Args:
            sql (str): SQL query to execute
            **kwargs: Additional parameters
            
        Returns:
            pd.DataFrame: Query results
        """
        if not self._ensure_connection():
            raise Exception("数据库未连接")
        
        try:
            # Get connection from pool
            conn = self.connection_pool.get_connection()
            if not conn:
                raise Exception("无法从连接池获取连接")
            
            cursor = conn.cursor(dictionary=True)
            
            # Ensure company_id filter is present when applicable
            sql = self._ensure_company_filter(sql)
            cursor.execute(sql)
            
            # Fetch results
            results = cursor.fetchall()
            cursor.close()
            
            # Convert to DataFrame
            df = pd.DataFrame(results)
            
            # Log the query execution
            print(f"✅ SQL执行成功，返回 {len(df)} 行数据")
            self._debug_log(
                'sql_execution',
                sql=sql,
                row_count=int(len(df)) if df is not None else 0
            )
            
            return df
            
        except Error as e:
            error_msg = f"SQL执行错误: {e}\nSQL: {sql}"
            print(f"❌ {error_msg}")
            self._debug_log('sql_execution_error', sql=sql, error=str(e))
            raise Exception(error_msg)
        finally:
            # Clean up cursor and connection
            if 'cursor' in locals() and cursor:
                try:
                    cursor.close()
                except Exception:
                    pass
            if 'conn' in locals() and conn:
                try:
                    conn.close()  # Return to pool
                except Exception:
                    pass
    
    def _initialize_vanna_training(self):
        """Initialize Vanna with database schema and training examples"""
        try:
            print("🔄 正在初始化Vanna训练数据...")
            
            # Add database schema information
            self._add_database_schema()
            
            # Add training examples
            self._add_training_examples()
            
            print("✅ Vanna训练数据初始化完成")
            
        except Exception as e:
            print(f"⚠️ Vanna训练数据初始化失败: {e}")
    
    def _add_database_schema(self):
        """Add database schema to Vanna"""
        if not self._ensure_connection():
            return
        
        conn = None
        cursor = None
        
        try:
            # Get connection from pool
            conn = self.connection_pool.get_connection()
            if not conn:
                print("⚠️ 无法从连接池获取连接，跳过数据库架构初始化")
                return
            
            cursor = conn.cursor()
            
            # Get all tables
            cursor.execute("SHOW TABLES")
            tables = [table[0] for table in cursor.fetchall()]
            
            table_count = 0
            for table in tables:
                try:
                    # Get table structure
                    cursor.execute(f"DESCRIBE `{table}`")
                    columns = cursor.fetchall()
                    
                    # Create DDL statement
                    ddl = f"CREATE TABLE `{table}` (\n"
                    column_definitions = []
                    
                    for column in columns:
                        field, type_, null, key, default, extra = column
                        col_def = f"  `{field}` {type_}"
                        if null == 'NO':
                            col_def += " NOT NULL"
                        if key == 'PRI':
                            col_def += " PRIMARY KEY"
                        if extra:
                            col_def += f" {extra}"
                        column_definitions.append(col_def)
                    
                    ddl += ",\n".join(column_definitions)
                    ddl += "\n);"
                    
                    # Add DDL to Vanna
                    self.add_ddl(ddl)
                    
                    # Add table documentation
                    doc = f"Table `{table}` contains {len(columns)} columns. "
                    if 'company_id' in [col[0] for col in columns]:
                        doc += "This table supports multi-tenancy with company_id filtering. "
                    
                    self.add_documentation(doc)
                    table_count += 1
                    
                    if table_count % 50 == 0:
                        print(f"  📊 已处理 {table_count}/{len(tables)} 个表...")
                        
                except Exception as e:
                    self.logger.error(f"❌ 添加表 {table} 的结构失败: {e}")
                    continue
            
            print(f"✅ 已添加 {table_count}/{len(tables)} 个表的结构信息到Vanna")
            self.logger.info(f"Vector DB schema updated: {table_count}/{len(tables)} tables added")
            
        except Exception as e:
            error_msg = f"❌ 添加数据库结构失败: {e}"
            print(error_msg)
            self.logger.error(error_msg, exc_info=True)
        finally:
            # Clean up cursor and connection
            if cursor:
                try:
                    cursor.close()
                except Exception:
                    pass
            if conn:
                try:
                    conn.close()  # Return to pool
                except Exception:
                    pass
    
    def refresh_vector_db_schema(self):
        """Refresh/reload the vector database schema from MySQL database"""
        try:
            self.logger.info("🔄 Starting vector DB schema refresh...")
            print("🔄 正在刷新向量数据库架构...")
            
            # Re-add database schema
            self._add_database_schema()
            
            # Re-add training examples
            self._add_training_examples()
            
            self.logger.info("✅ Vector DB schema refresh completed")
            print("✅ 向量数据库架构刷新完成")
            
            return {
                "status": "success",
                "message": "Vector DB schema refreshed successfully"
            }
            
        except Exception as e:
            error_msg = f"❌ 刷新向量数据库架构失败: {e}"
            print(error_msg)
            self.logger.error(error_msg, exc_info=True)
            return {
                "status": "error",
                "message": str(e)
            }
    
    def get_vector_db_status(self):
        """Get status information about the vector database"""
        try:
            import chromadb
            client = chromadb.PersistentClient(path='.')
            collections = client.list_collections()
            
            # Get table count from MySQL
            mysql_table_count = 0
            mysql_tables = []
            if self._ensure_connection():
                conn = None
                cursor = None
                try:
                    conn = self.connection_pool.get_connection()
                    if conn:
                        cursor = conn.cursor()
                        cursor.execute("SHOW TABLES")
                        mysql_tables = [table[0] for table in cursor.fetchall()]
                        mysql_table_count = len(mysql_tables)
                finally:
                    if cursor:
                        try:
                            cursor.close()
                        except Exception:
                            pass
                    if conn:
                        try:
                            conn.close()  # Return to pool
                        except Exception:
                            pass
            
            # Count how many tables are actually indexed in ChromaDB
            # Check the "ddl" collection (where table DDLs are stored)
            indexed_table_count = 0
            indexed_tables = []
            collection_details = {}
            
            try:
                for col in collections:
                    col_name = col.name
                    col_count = col.count()
                    collection_details[col_name] = col_count
                    
                    # The "ddl" collection stores table DDLs
                    # Each table should have at least one DDL document
                    if col_name == "ddl":
                        # Try to get all DDL documents to count unique tables
                        try:
                            # Get all documents from the collection
                            results = col.get(limit=10000)  # Get up to 10k documents
                            if results and 'ids' in results:
                                indexed_table_count = len(results['ids'])
                                
                                # Try to extract table names from DDL documents
                                if 'documents' in results:
                                    for doc in results['documents']:
                                        # Extract table name from CREATE TABLE statement
                                        match = re.search(r'CREATE TABLE\s+`?(\w+)`?', doc, re.IGNORECASE)
                                        if match:
                                            table_name = match.group(1)
                                            if table_name not in indexed_tables:
                                                indexed_tables.append(table_name)
                        except Exception as e:
                            self.logger.warning(f"Could not count DDL documents: {e}")
                            # Fallback: use collection count as estimate
                            indexed_table_count = col_count
            except Exception as e:
                self.logger.warning(f"Error counting indexed tables: {e}")
            
            # Check chroma.sqlite3 file
            import os
            chroma_file = "chroma.sqlite3"
            file_size = 0
            file_modified = None
            if os.path.exists(chroma_file):
                stat = os.stat(chroma_file)
                file_size = stat.st_size
                file_modified = datetime.fromtimestamp(stat.st_mtime).isoformat()
            
            # Calculate coverage percentage
            coverage_percent = 0
            if mysql_table_count > 0:
                coverage_percent = round((indexed_table_count / mysql_table_count) * 100, 2)
            
            # Check for missing tables
            missing_tables = []
            if mysql_tables and indexed_tables:
                missing_tables = [t for t in mysql_tables if t not in indexed_tables]
            
            status = {
                "collections_count": len(collections),
                "collections": {name: collection_details.get(name, 0) for name in [col.name for col in collections]},
                "mysql_table_count": mysql_table_count,
                "indexed_table_count": indexed_table_count,
                "indexed_tables_sample": indexed_tables[:20] if indexed_tables else [],
                "coverage_percent": coverage_percent,
                "missing_tables_count": len(missing_tables),
                "missing_tables_sample": missing_tables[:20] if missing_tables else [],
                "chroma_file_size_mb": round(file_size / (1024 * 1024), 2) if file_size > 0 else 0,
                "chroma_file_modified": file_modified,
                "status": "healthy" if coverage_percent >= 95 else "needs_refresh"
            }
            
            # Add warning if coverage is low
            if coverage_percent < 95:
                status["warning"] = f"Only {coverage_percent}% of tables are indexed. Consider refreshing the vector DB schema."
            
            return status
            
        except Exception as e:
            self.logger.error(f"Error getting vector DB status: {e}", exc_info=True)
            return {
                "error": str(e),
                "status": "error"
            }
    
    def _add_training_examples(self):
        """Add training examples to Vanna"""
        training_examples = [
            {
                "question": "显示所有发票信息",
                "sql": "SELECT * FROM `invoices` LIMIT 100"
            },
            {
                "question": "统计总收入",
                "sql": "SELECT SUM(COALESCE(`total`, 0)) as total_revenue FROM `invoices` WHERE `status` = 'paid'"
            },
            {
                "question": "查看员工列表",
                "sql": "SELECT * FROM `users` LIMIT 100"
            },
            {
                "question": "统计本月发票数量",
                "sql": "SELECT COUNT(*) as invoice_count FROM `invoices` WHERE MONTH(`issue_date`) = MONTH(CURDATE()) AND YEAR(`issue_date`) = YEAR(CURDATE())"
            },
            {
                "question": "查看供应商信息",
                "sql": "SELECT * FROM `purchase_vendors` LIMIT 100"
            },
            {
                "question": "统计员工考勤记录",
                "sql": "SELECT COUNT(*) as attendance_count FROM `attendances`"
            },
            {
                "question": "查看项目列表",
                "sql": "SELECT * FROM `projects` LIMIT 100"
            },
            {
                "question": "统计费用支出",
                "sql": "SELECT SUM(COALESCE(`price`, 0)) as total_expenses FROM `expenses`"
            }
        ]
        
        for example in training_examples:
            try:
                self.add_question_sql(question=example["question"], sql=example["sql"])
            except Exception as e:
                print(f"⚠️ 添加训练示例失败: {e}")
        
        print(f"✅ 已添加 {len(training_examples)} 个训练示例到Vanna")
    
    def get_table_list(self) -> list:
        """获取数据库中所有表的列表"""
        if not self._ensure_connection():
            return []
        
        conn = None
        cursor = None
        
        try:
            conn = self.connection_pool.get_connection()
            if not conn:
                print("⚠️ 无法从连接池获取连接")
                return []
            
            cursor = conn.cursor()
            cursor.execute("SHOW TABLES")
            tables = [table[0] for table in cursor.fetchall()]
            return tables
        except Error as e:
            print(f"获取表列表时出错: {e}")
            return []
        finally:
            if cursor:
                try:
                    cursor.close()
                except Exception:
                    pass
            if conn:
                try:
                    conn.close()  # Return to pool
                except Exception:
                    pass
    
    def refresh_schema_cache(self):
        """清理所有与schema相关的缓存"""
        self._schema_cache = None
        self._schema_summary_cache = None
        self._table_catalog_cache = None
        self._table_domain_reference_cache = None
        self._tables_with_company_id_cache = None
        self._update_schema_guardrails()
    
    def _get_table_catalog(self):
        """构建表目录，包括按业务域划分的统计信息"""
        if self._table_catalog_cache is not None:
            return self._table_catalog_cache
        
        tables = sorted(self.get_table_list())
        domain_reference = self._get_table_domain_reference()
        domain_assignments = {}
        assigned_tables = set()
        
        for domain, table_map in domain_reference.items():
            domain_tables = sorted([t for t in table_map.keys() if t in tables])
            domain_assignments[domain] = domain_tables
            assigned_tables.update(domain_tables)
        
        unassigned_tables = sorted([t for t in tables if t not in assigned_tables])
        
        catalog = {
            'tables': tables,
            'count': len(tables),
            'domains': domain_assignments,
            'unassigned': unassigned_tables
        }
        self._table_catalog_cache = catalog
        return catalog
    
    def get_schema_summary(self, max_chars: int = 2500) -> str:
        """
        返回数据库结构概要，包含总表数、业务域分布和关键注意事项
        
        Args:
            max_chars: 结果文本的最大长度，None 表示不截断
        """
        if self._schema_summary_cache is None:
            try:
                self._schema_summary_cache = self._build_schema_summary()
            except Error as e:
                return f"获取数据库结构时出错: {e}"
            except Exception as e:
                return f"构建数据库概要时出错: {e}"
        
        summary = self._schema_summary_cache
        if isinstance(max_chars, int) and max_chars > 0 and len(summary) > max_chars:
            truncated = summary[:max_chars].rstrip()
            return truncated + "\n...（内容已截断，可使用 SHOW TABLES 查看完整表清单）"
        return summary
    
    def _build_schema_summary(self, max_tables_per_domain: int = 8) -> str:
        """生成详细的数据库结构概要文本"""
        catalog = self._get_table_catalog()
        total_tables = catalog['count']
        
        if total_tables == 0:
            return "数据库未连接或未检索到任何表。"
        
        domain_reference = self._get_table_domain_reference()
        lines = []
        lines.append("=== Database Overview / 数据库总览 ===")
        lines.append(f"- Total tables: {total_tables}（全部可用于分析）")
        if self.company_filter_enabled and isinstance(self.current_company_id, int):
            lines.append(f"- 默认公司范围：company_id = {self.current_company_id}（请在SQL中保持该过滤）")
        else:
            lines.append("- 当前默认范围包含所有company_id（多租户环境，请谨慎过滤）")
        lines.append("- 多租户系统：尽量在包含company_id的表上增加company_id过滤。")
        lines.append("")
        
        lines.append("=== Domain Highlights / 业务域概览 ===")
        for domain, table_map in domain_reference.items():
            domain_tables = catalog['domains'].get(domain, [])
            if not domain_tables:
                continue
            lines.append(f"{domain}（{len(domain_tables)} 表）:")
            for table_name in domain_tables[:max_tables_per_domain]:
                desc = table_map.get(table_name, '')
                bullet = f"  • {table_name}"
                if desc:
                    bullet += f": {desc}"
                lines.append(bullet)
            remaining = len(domain_tables) - max_tables_per_domain
            if remaining > 0:
                lines.append(f"  • ... 另有 {remaining} 个相关表（如需列表可使用 `SHOW TABLES`）")
            lines.append("")
        
        if catalog['unassigned']:
            lines.append(f"其他业务相关表（共 {len(catalog['unassigned'])} 表，示例）:")
            for table_name in catalog['unassigned'][:max_tables_per_domain]:
                lines.append(f"  • {table_name}")
            remaining = len(catalog['unassigned']) - max_tables_per_domain
            if remaining > 0:
                lines.append(f"  • ... 还有 {remaining} 个未分类表")
            lines.append("")
        
        lines.append("=== Usage Guidance / 使用提示 ===")
        for warning in self._schema_guardrails:
            lines.append(f"- {warning}")
        lines.append("- 如需查看某个表的详细字段，请使用 `SHOW COLUMNS FROM table_name` 或 `DESCRIBE table_name`。")
        lines.append("- 需要快速预览数据时，可执行 `SELECT * FROM table_name LIMIT 5`。")
        lines.append("- 如果需要完整表清单，可执行 `SHOW TABLES`，本概要仅展示重点信息以控制提示长度。")
        
        return "\n".join(lines).strip()

    def _build_domain_prefix_catalog(self):
        """
        定义用于自动识别业务域的前缀和关键字集合。
        该结构提供匹配规则，实际表映射将在运行时动态生成。
        """
        return {
            '人力资源管理': {
                'prefixes': ['employee_', 'hr_', 'attendance', 'leave_', 'payroll_', 'salary_', 'designation', 'biometric'],
                'keywords': ['employee', 'attendance', 'payroll', 'hr', 'leave', 'salary'],
                'explicit': {
                    'roles': '角色/权限管理表',
                    'skills': '技能配置表',
                    'holidays': '节假日设置表'
                }
            },
            '招聘管理': {
                'prefixes': ['recruit_', 'application_', 'candidate_', 'interview_'],
                'keywords': ['recruit', 'candidate', 'interview', 'job_application'],
                'explicit': {}
            },
            '客户关系管理': {
                'prefixes': ['client_', 'lead_', 'pipeline_', 'prospect_'],
                'keywords': ['client', 'lead', 'pipeline', 'prospect', 'deals'],
                'explicit': {
                    'deals': '交易线索主表（使用next_follow_up标识跟进状态）'
                }
            },
            '项目管理': {
                'prefixes': ['project_', 'task', 'milestone', 'sprint'],
                'keywords': ['project', 'task', 'milestone', 'kanban'],
                'explicit': {}
            },
            '财务管理': {
                'prefixes': ['invoice', 'payment', 'expense', 'credit_note', 'estimate', 'proposal', 'subscription', 'finance_', 'budget'],
                'keywords': ['invoice', 'payment', 'expense', 'finance', 'profit', 'revenue', 'account'],
                'explicit': {
                    'currencies': '货币及汇率配置表'
                }
            },
            '采购管理': {
                'prefixes': ['purchase_', 'vendor_', 'inventory_', 'stock_'],
                'keywords': ['purchase', 'vendor', 'inventory', 'procur'],
                'explicit': {}
            },
            '产品管理': {
                'prefixes': ['product_', 'catalog_', 'sku_', 'item_'],
                'keywords': ['product', 'catalog', 'sku', 'item'],
                'explicit': {}
            },
            '资产管理': {
                'prefixes': ['asset_', 'depreciation_', 'maintenance_'],
                'keywords': ['asset', 'depreciation', 'maintenance'],
                'explicit': {}
            },
            '系统与配置': {
                'prefixes': ['module_', 'settings', 'config', 'custom_', 'dashboard_', 'integration_', 'oauth_', 'api_', 'webhook_'],
                'keywords': ['setting', 'config', 'module', 'integration', 'notification'],
                'explicit': {
                    'file_storage': '文件存储/附件配置表',
                    'google_calendar_modules': '谷歌日历集成配置',
                    'slack_settings': 'Slack 集成配置表'
                }
            },
            '知识与支持': {
                'prefixes': ['knowledge_', 'support_', 'ticket', 'faq_', 'doc_', 'guide_', 'discussion_', 'notice_', 'event_'],
                'keywords': ['knowledge', 'ticket', 'faq', 'discussion', 'support', 'notice', 'event'],
                'explicit': {}
            },
            '合同与文档': {
                'prefixes': ['contract_', 'letter_', 'document_', 'signature_', 'proposal_'],
                'keywords': ['contract', 'document', 'letter', 'signature', 'proposal'],
                'explicit': {}
            }
        }

    def _auto_table_description(self, table_name: str) -> str:
        """根据表名自动生成简要中文描述"""
        words = table_name.replace('_', ' ').strip().title()
        return f"{words} 表（自动归类）"

    def _get_table_domain_reference(self):
        """根据当前数据库表动态构建业务域映射"""
        if self._table_domain_reference_cache is not None:
            return self._table_domain_reference_cache

        tables = set(self.get_table_list())
        domain_map = {}
        assigned_tables = set()
        prefix_catalog = self._domain_prefix_catalog or {}

        for domain, config in prefix_catalog.items():
            domain_entries = {}
            prefixes = config.get('prefixes', [])
            keywords = config.get('keywords', [])
            explicit = config.get('explicit', {})

            for table in list(tables):
                description = None
                if table in explicit:
                    description = explicit[table]
                else:
                    if any(table.startswith(prefix) for prefix in prefixes):
                        description = self._auto_table_description(table)
                    elif any(keyword in table for keyword in keywords):
                        description = self._auto_table_description(table)

                if description:
                    domain_entries[table] = description
                    assigned_tables.add(table)

            if domain_entries:
                domain_map[domain] = domain_entries

        for domain, config in prefix_catalog.items():
            explicit = config.get('explicit', {})
            for table, description in explicit.items():
                if table in tables and table not in assigned_tables:
                    domain_map.setdefault(domain, {})[table] = description
                    assigned_tables.add(table)

        self._table_domain_reference_cache = domain_map
        return domain_map

    def _update_schema_guardrails(self):
        """根据最新表数量和公司过滤状态刷新提示信息"""
        table_count = len(self.get_table_list())
        if self.company_filter_enabled and isinstance(self.current_company_id, int):
            company_note = f"保持公司隔离：若表存在company_id字段，必须加入company_id过滤条件（默认company_id = {self.current_company_id}）。"
        else:
            company_note = "当前无默认company_id过滤，如跨租户查询请谨慎确认权限。"

        self._schema_guardrails = [
            "deals表没有client_id列，如需客户信息请通过leads表关联client_details。",
            "deals表没有product_id列，如需产品维度请关联销售明细或产品表。",
            "product_reviews表不存在，请勿生成引用该表的查询。",
            f"当前数据库检测到 {table_count} 张表，可使用 SHOW TABLES 或 schema 概要查看完整清单。",
            "多数业务表包含created_at / updated_at 字段，财务类表常用issue_date或paid_on，请根据业务语义选择正确的日期字段。",
            company_note
        ]
    

    def get_schema_info(self):
        """向后兼容的schema获取方法，返回概要信息"""
        if self._schema_cache:
            return self._schema_cache
        
        summary = self.get_schema_summary(max_chars=None)
        self._schema_cache = summary
        return summary

        """
        以下为保留的旧实现，仅作为参考，不再执行：
        
        if not self._ensure_connection():
            return "数据库未连接"
        
        try:
            cursor = self.connection.cursor()
            
            # 获取所有表
            cursor.execute("SHOW TABLES")
            tables = [table[0] for table in cursor.fetchall()]
            
            schema_info = []
            schema_info.append("=== E3企业管理系统数据库结构 ===\n")
            schema_info.append("重要说明：")
            schema_info.append("1. 这是一个多租户系统，使用company_id字段区分不同公司的数据")
            if self.company_filter_enabled and self.current_company_id is not None:
                schema_info.append(f"2. **当前查询范围：仅限company_id = {self.current_company_id}的数据**")
            else:
                schema_info.append("2. 当前查询范围包含所有company_id的数据，请注意结果可能涉及多个公司。")
            schema_info.append("3. 没有独立的companies表，公司信息通过company_id关联")
            schema_info.append("4. 当用户询问'有多少个公司'时，应该查询DISTINCT company_id的数量")
            schema_info.append("5. 公司相关信息可能存储在company_addresses表中")
            schema_info.append("6. 员工信息主要存储在employee_details表中，通过user_id关联users表")
            schema_info.append("7. 当用户询问'有多少个员工'时，应该查询employee_details表的记录数量")
            schema_info.append("8. 客户信息存储在client_details表中，包含company_name字段")
            schema_info.append("9. 供应商信息存储在purchase_vendors表中，包含company_name字段")
            schema_info.append("10. **deals表重要说明：没有status字段！使用next_follow_up字段('yes'/'no')表示是否需要跟进**")
            schema_info.append("11. **deals表主要字段：id, name, value, close_date, next_follow_up, lead_id, agent_id, created_at, updated_at**")
            if self.company_filter_enabled and self.current_company_id is not None:
                schema_info.append("12. **所有查询必须包含company_id过滤条件，确保数据安全和准确性**")
            schema_info.append("")

            if tables:
                schema_info.append(f"=== 数据库完整表清单 (共 {len(tables)} 个表) ===")
                schema_info.append("**重要：系统可以访问以下所有表，请根据用户问题选择合适的表进行查询**")
                for table in tables:
                    schema_info.append(f"  - {table}")
                schema_info.append("")
                schema_info.append(f"**总计：{len(tables)} 个表全部可用**")
                schema_info.append("")
            
            # 按业务领域分类的表结构说明
            table_categories = {
                '人力资源管理': {
                    'employee_details': '员工详情表（主要员工信息表）',
                    'biometric_employees': '生物识别员工表',
                    'employee_docs': '员工文档表',
                    'employee_shifts': '员工班次表',
                    'employee_shift_schedules': '员工班次计划表',
                    'employee_skills': '员工技能表',
                    'employee_monthly_salaries': '员工月薪表',
                    'employee_payroll_cycles': '员工薪资周期表',
                    'attendances': '考勤记录表',
                    'attendance_settings': '考勤设置表',
                    'biometric_devices': '生物识别设备表',
                    'biometric_device_attendances': '生物识别设备考勤表',
                    'biometric_commands': '生物识别命令表',
                    'biometric_settings': '生物识别设置表',
                    'leaves': '请假记录表',
                    'leave_types': '请假类型表',
                    'leave_settings': '请假设置表',
                    'holidays': '节假日表',
                    'designations': '职位表',
                    'roles': '角色表',
                    'skills': '技能表',
                    'emergency_contacts': '紧急联系人表',
                    'awards': '奖励表',
                    'appreciations': '表彰表',
                    'salary_payment_methods': '薪资支付方式表',
                    'salary_slips': '工资条表',
                    'payroll_settings': '薪资设置表'
                },
                '招聘管理': {
                    'application_sources': '招聘来源表',
                    'recruit_jobs': '招聘职位表',
                    'recruit_job_applications': '求职申请表',
                    'recruit_candidate_database': '候选人数据库表',
                    'recruit_interview_schedules': '面试安排表',
                    'recruit_interview_stages': '面试阶段表',
                    'recruit_application_status': '申请状态表',
                    'recruit_application_status_categories': '申请状态分类表',
                    'recruit_job_categories': '职位分类表',
                    'recruit_job_sub_categories': '职位子分类表',
                    'recruit_job_types': '职位类型表',
                    'recruit_job_alerts': '职位提醒表',
                    'recruit_job_offer_letter': '录用通知书表',
                    'recruit_custom_questions': '自定义问题表',
                    'recruit_email_notification_settings': '招聘邮件通知设置表',
                    'recruit_skills': '招聘技能表',
                    'recruit_work_experiences': '工作经验表',
                    'recruiters': '招聘人员表'
                },
                '客户关系管理': {
                    'client_details': '客户详情表（包含公司名称）',
                    'client_categories': '客户分类表',
                    'client_sub_categories': '客户子分类表',
                    'client_notes': '客户备注表',
                    'leads': '潜在客户表',
                    'lead_agents': '潜在客户代理表',
                    'lead_category': '潜在客户分类表',
                    'lead_sources': '潜在客户来源表',
                    'lead_status': '潜在客户状态表',
                    'lead_pipelines': '潜在客户管道表',
                    'lead_custom_forms': '潜在客户自定义表单表',
                    'lead_setting': '潜在客户设置表',
                    'pipeline_stages': '管道阶段表',
                    'deals': '交易表（注意：没有status字段，使用next_follow_up字段表示跟进状态）'
                },
                '项目管理': {
                    'projects': '项目表',
                    'project_category': '项目分类表',
                    'project_milestones': '项目里程碑表',
                    'project_settings': '项目设置表',
                    'project_status_settings': '项目状态设置表',
                    'project_time_logs': '项目时间日志表',
                    'project_time_log_breaks': '项目时间日志休息表',
                    'project_label_list': '项目标签列表表',
                    'tasks': '任务表',
                    'taskboard_columns': '任务板列表'
                },
                '财务管理': {
                    'invoices': '发票表',
                    'invoice_settings': '发票设置表',
                    'invoice_payment_details': '发票支付详情表',
                    'payments': '支付表',
                    'payment_gateway_credentials': '支付网关凭证表',
                    'offline_payment_methods': '线下支付方式表',
                    'expenses': '费用表',
                    'expenses_category': '费用分类表',
                    'expenses_category_roles': '费用分类角色表',
                    'expenses_recurring': '循环费用表',
                    'credit_notes': '贷项通知单表',
                    'estimates': '报价表',
                    'proposals': '提案表',
                    'proposal_templates': '提案模板表',
                    'proposal_template_items': '提案模板项目表',
                    'proposal_template_item_images': '提案模板项目图片表',
                    'currencies': '货币表',
                    'global_invoices': '全局发票表',
                    'global_subscriptions': '全局订阅表'
                },
                '采购管理': {
                    'purchase_vendors': '供应商表（包含公司名称）',
                    'purchase_vendor_categories': '供应商分类表',
                    'purchase_vendor_histories': '供应商历史表',
                    'purchase_vendor_payments': '供应商支付表',
                    'purchase_vendor_credits': '供应商贷项表',
                    'purchase_vendor_credit_histories': '供应商贷项历史表',
                    'purchase_orders': '采购订单表',
                    'purchase_order_histories': '采购订单历史表',
                    'purchase_bills': '采购账单表',
                    'purchase_bill_histories': '采购账单历史表',
                    'purchase_items': '采购项目表',
                    'purchase_payment_histories': '采购支付历史表',
                    'purchase_product_histories': '采购产品历史表',
                    'purchase_settings': '采购设置表',
                    'purchase_notification_settings': '采购通知设置表',
                    'purchase_inventory_adjustment': '采购库存调整表',
                    'purchase_inventory_histories': '采购库存历史表',
                    'purchase_stock_adjustments': '采购库存调整表',
                    'purchase_stock_adjustment_reasons': '采购库存调整原因表'
                },
                '产品管理': {
                    'products': '产品表',
                    'product_category': '产品分类表',
                    'product_sub_category': '产品子分类表',
                    'product_files': '产品文件表'
                },
                '资产管理': {
                    'assets': '资产表',
                    'asset_types': '资产类型表'
                },
                '系统设置': {
                    'company_addresses': '公司地址表',
                    'custom_fields': '自定义字段表',
                    'custom_field_groups': '自定义字段组表',
                    'custom_link_settings': '自定义链接设置表',
                    'dashboard_widgets': '仪表板小部件表',
                    'email_notification_settings': '邮件通知设置表',
                    'message_settings': '消息设置表',
                    'module_settings': '模块设置表',
                    'file_storage': '文件存储表',
                    'google_calendar_modules': '谷歌日历模块表',
                    'slack_settings': 'Slack设置表',
                    'quick_books_settings': 'QuickBooks设置表'
                },
                '知识管理': {
                    'knowledge_bases': '知识库表',
                    'knowledge_categories': '知识分类表',
                    'support_tickets': '支持工单表'
                },
                '通知与沟通': {
                    'notices': '通知表',
                    'notice_views': '通知查看表',
                    'events': '事件表',
                    'event_attendees': '事件参与者表',
                    'sticky_notes': '便签表',
                    'pinned': '置顶表',
                    'discussion_categories': '讨论分类表'
                },
                '合同管理': {
                    'contracts': '合同表',
                    'contract_signs': '合同签署表',
                    'contract_templates': '合同模板表',
                    'contract_types': '合同类型表'
                },
                '文档管理': {
                    'letters': '信件表',
                    'letter_templates': '信件模板表',
                    'log_time_for': '时间记录表'
                }
            }
            
            # 核心重要表（用于详细显示结构）
            # NOTE: This list is ONLY for detailed column documentation display
            # The system can access ALL tables - see "数据库完整表清单" section above
            # This is NOT a restriction - all 462 tables are accessible
            important_tables = [
                'employee_details', 'attendances', 'client_details', 'deals', 'leads', 
                'projects', 'tasks', 'invoices', 'payments', 'expenses', 'products',
                'purchase_vendors', 'purchase_orders', 'company_addresses'
            ]
            
            # 首先显示核心重要表的详细结构
            schema_info.append("=== 核心业务表结构（详细列信息） ===")
            schema_info.append(f"**注意：以下 {len(important_tables)} 个表有详细结构说明，但系统可以访问所有 {len(tables)} 个表**")
            for table_name in important_tables:
                if table_name in tables:
                    try:
                        cursor.execute(f"DESCRIBE `{table_name}`")
                        columns = cursor.fetchall()
                        # 查找表的描述
                        description = ""
                        for category, tables_dict in table_categories.items():
                            if table_name in tables_dict:
                                description = tables_dict[table_name]
                                break
                        
                        schema_info.append(f"\n{table_name} ({description}):")
                        for column in columns:
                            col_name, col_type, null, key, default, extra = column
                            key_info = f" ({key})" if key else ""
                            schema_info.append(f"  - {col_name}: {col_type}{key_info}")
                    except Exception as e:
                        schema_info.append(f"\n{table_name}: 表不存在或无法访问")
            
            # 按业务领域分类显示所有表
            categorized_count = sum(len(tables_dict) for tables_dict in table_categories.values())
            schema_info.append(f"\n\n=== 按业务领域分类的表 (共 {categorized_count} 个表) ===")
            schema_info.append("**这些表按业务功能分类，便于理解，但系统可以访问所有表**")
            for category, tables_dict in table_categories.items():
                schema_info.append(f"\n【{category}】")
                for table_name, description in tables_dict.items():
                    schema_info.append(f"  - {table_name}: {description}")
            
            # 检查是否有遗漏的表
            categorized_tables = set()
            for tables_dict in table_categories.values():
                categorized_tables.update(tables_dict.keys())
            
            missing_tables = set(tables) - categorized_tables
            if missing_tables:
                schema_info.append(f"\n\n=== 其他表 (共 {len(missing_tables)} 个未分类表，全部可用) ===")
                schema_info.append("**这些表虽然未在业务分类中列出，但同样可以正常查询使用**")
                for table_name in sorted(missing_tables):
                    schema_info.append(f"  - {table_name}")
                schema_info.append(f"**未分类表总计：{len(missing_tables)} 个**")
            
            # 添加业务规则说明
            schema_info.append("\n\n=== 重要业务规则 ===")
            schema_info.append("1. deals表没有status字段，使用next_follow_up字段表示跟进状态")
            schema_info.append("2. **CRITICAL: deals表没有client_id列！deals表通过lead_id关联leads表，leads表有client_id**")
            schema_info.append("3. **CRITICAL: deals表没有product_id列！deals表不直接关联products表**")
            schema_info.append("4. **CRITICAL: product_reviews表不存在！不要使用此表**")
            schema_info.append("5. employee_details是主要的员工信息表，包含员工基本信息")
            schema_info.append("6. client_details和purchase_vendors表都包含公司名称信息")
            schema_info.append("7. 查询员工信息时优先使用employee_details表")
            schema_info.append("8. 考勤相关查询使用attendances表")
            schema_info.append("9. 项目和任务管理分别使用projects和tasks表")
            schema_info.append("10. 财务相关查询涉及invoices、payments、expenses等表")
            schema_info.append("11. 招聘相关查询使用recruit_开头的表")
            schema_info.append("12. 采购相关查询使用purchase_开头的表")
            schema_info.append("13. 人力资源管理涉及员工、考勤、请假、薪资等多个表")
            
            cursor.close()
            
            self._schema_cache = "\n".join(schema_info)
            return self._schema_cache
            
        except Error as e:
            return f"获取数据库结构时出错: {e}"
        """
    
    def generate_sql(self, question, mode=None, session_id=None):
        """Generate SQL query based on question using Vanna framework"""
        
        # 选择模型（不区分大小写处理 Flash 模式）
        mode_lower = (mode or '').lower()
        current_model = self.fast_model if mode_lower == 'flash' and self.fast_model else self.model

        # 检查是否是关于表数量的元问题
        # Remove [Flash], [DataReports], etc. prefixes for detection
        question_clean = re.sub(r'^\[.*?\]\s*', '', question, flags=re.IGNORECASE)
        question_lower = question_clean.lower()
        is_meta_question = any(keyword in question_lower for keyword in [
            'how many tables', 'how many table', 'how many table can', 'how many table do',
            'table count', 'number of tables', 'total tables', 'accessible tables',
            'can access', 'can you access', 'available tables', 'tables available',
            'tables can you', 'tables do you', 'tables are available', 'tables are accessible',
            'how many tables can', 'how many tables do', 'how many tables are'
        ])
        
        # 如果是元问题，使用完整schema信息而不是Vanna的RAG
        if is_meta_question:
            print("🤖 检测到表数量元问题，使用完整schema信息")
            try:
                schema_info = self.get_schema_info()
                tables = self.get_table_list()
                table_count = len(tables)
                
                # 直接返回查询表数量的SQL
                sql = f"SELECT COUNT(*) as total_tables FROM information_schema.tables WHERE table_schema = '{self.mysql_config['database']}'"
                print(f"✅ 表数量查询SQL: {sql}")
                return sql
            except Exception as e:
                print(f"⚠️ 元问题处理失败，降级到Vanna: {e}")

        # 使用Vanna框架生成SQL
        try:
            print("🤖 使用Vanna框架生成SQL")
            
            # 构建增强的问题，包含必要的SQL约束
            rules = []
            if self.company_filter_enabled and self.current_company_id is not None:
                rules.append(f"所有查询必须限制在company_id = {self.current_company_id}的数据范围内")
                rules.append("如果查询的表包含company_id字段，必须添加WHERE条件过滤")
            else:
                rules.append("如果查询需要区分公司，请在SQL中显式处理company_id字段")
            rules.append("使用反引号包围表名和列名")
            rules.append("对于聚合函数，使用COALESCE处理NULL值")

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
            
            # 使用Vanna的generate_sql方法
            sql = super().generate_sql(enhanced_question, allow_llm_to_see_data=False)
            
            if not sql:
                raise Exception("Vanna未能生成SQL")
            
            # 后处理：确保SQL包含company_id过滤条件
            raw_sql = sql
            company_sql = self._ensure_company_filter(raw_sql)
            # 后处理：根据问题自动注入日期过滤
            date_sql = self._ensure_date_filter(company_sql, question)
            # 后处理：保护聚合函数的空值逻辑
            final_sql = self._sanitize_aggregates(date_sql)
            self._debug_log(
                'generate_sql',
                mode=mode,
                question=question,
                session_id=session_id,
                raw_sql=raw_sql,
                company_sql=company_sql,
                date_sql=date_sql,
                final_sql=final_sql
            )
            
            print(f"✅ Vanna生成SQL成功: {final_sql[:100]}...")
            return final_sql
        
        except Exception as e:
            print(f"❌ Vanna生成SQL失败: {e}")
            
            # 降级到传统方法
            print("🔄 降级到传统LLM方法")
            return self._generate_sql_fallback(question, mode, session_id)
    
    def _generate_sql_fallback(self, question, mode=None, session_id=None):
        """Fallback SQL generation method using traditional LLM approach"""
        schema_info = self.get_schema_summary(max_chars=3500)
        tables = self.get_table_list()
        table_count = len(tables)
        
        # 选择模型
        mode_lower = (mode or '').lower()
        current_model = self.fast_model if mode_lower == 'flash' and self.fast_model else self.model
        
        system_prompt = f"""你是一个专业的SQL查询生成助手。基于以下数据库结构，为用户问题生成准确的SQL查询。

**重要：数据库包含 {table_count} 个表，系统可以访问所有表。**

**⚠️ 关键警告 - 禁止使用:**
- ❌ deals表没有client_id列！deals表通过lead_id关联leads表，leads表有client_id
- ❌ deals表没有product_id列！deals表不直接关联products表
- ❌ deals表没有amount列！应该使用value列（deals表主要字段：id, name, value, close_date, next_follow_up, lead_id, agent_id, created_at, updated_at）
- ❌ product_reviews表不存在！不要使用此表
- ❌ expense_categories表不存在！expenses表可能使用category_id列直接关联其他表
- ❌ clients表不存在！应该使用client_details表

**✅ 关键列名修正:**
- ✅ invoices表使用issue_date列（不是invoice_date！）
- ✅ expenses表使用purchase_date列（不是payment_date！）
- ✅ payments表使用paid_on列（不是payment_date！）
- ✅ expenses表没有balance列
- ✅ deals表使用value列（不是amount！deals表主要字段：id, name, value, close_date, next_follow_up, lead_id, agent_id, created_at, updated_at）

数据库结构:
{schema_info}

重要规则:
1. 只返回SQL查询语句，不要包含任何解释
2. 使用反引号包围表名和列名
3. 确保SQL语法正确
4. **关键：验证表/列是否存在。不要使用deals.client_id、deals.product_id或product_reviews表**
5. **关键：使用正确的列名：invoices.issue_date（不是invoice_date）、expenses.purchase_date（不是payment_date）、payments.paid_on（不是payment_date）、deals.value（不是deals.amount）**
6. 对于中文字段值，使用UTF-8编码
7. 返回所有相关数据，除非用户明确要求限制数量（如"前10个"、"最多5条"等）
8. 使用适当的WHERE条件过滤数据
9. 对于日期查询，使用正确的日期格式
{self._build_company_filter_guidance()}

用户问题: {question}

请生成对应的SQL查询:"""
        
        # Inject conversation memory if available
        _mem = self._build_memory_summary(session_id) if session_id else ""
        if _mem:
            system_prompt = system_prompt.replace("用户问题:", f"会话记忆（供参考）：\n{_mem}\n\n用户问题:")

        try:
            response = self.client.chat.completions.create(
                model=current_model,
                messages=[
                    {"role": "system", "content": system_prompt},
                    {"role": "user", "content": question}
                ],
                temperature=0.1,
                max_tokens=1000
            )
            
            sql = response.choices[0].message.content.strip()
            
            # 清理SQL语句
            if sql.startswith('```sql'):
                sql = sql[6:]
            if sql.startswith('```'):
                sql = sql[3:]
            if sql.endswith('```'):
                sql = sql[:-3]
            
            sql = sql.strip()
            
            # 后处理：确保SQL包含company_id过滤条件
            raw_sql = sql
            company_sql = self._ensure_company_filter(raw_sql)
            # 后处理：根据问题自动注入日期过滤
            date_sql = self._ensure_date_filter(company_sql, question)
            # 后处理：保护聚合函数的空值逻辑
            final_sql = self._sanitize_aggregates(date_sql)
            self._debug_log(
                'generate_sql_fallback',
                mode=mode,
                question=question,
                session_id=session_id,
                raw_sql=raw_sql,
                company_sql=company_sql,
                date_sql=date_sql,
                final_sql=final_sql
            )
            
            return final_sql
        
        except Exception as e:
            print(f"❌ 降级SQL生成也失败: {e}")
            return None
    
    def _ensure_company_filter(self, sql):
        """Ensure SQL queries include company_id filter when enabled."""
        original_sql = sql
        if not sql or sql.startswith("生成SQL时出错"):
            self._debug_log('company_filter_skip', reason='invalid_sql', original_sql=original_sql)
            return sql
        if not self.company_filter_enabled or self.current_company_id is None:
            self._debug_log('company_filter_skip', reason='filter_disabled', original_sql=original_sql)
            return sql
        
        # 转换为小写进行检查，但保持原始SQL的大小写
        sql_lower = sql.lower()

        # 如果已经包含company_id过滤条件（兼容反引号与表前缀），直接返回
        import re
        id_val = self.current_company_id
        pattern = re.compile(r"(?:`?\w+`?\.)?`?company_id`?\s*=\s*" + re.escape(str(id_val)))
        if pattern.search(sql):
            self._debug_log('company_filter_skip', reason='already_present', original_sql=original_sql)
            return sql

        # 获取SQL中涉及的表名
        tables_with_company_id = self._get_tables_with_company_id()

        # 检查SQL中是否涉及包含company_id的表
        involved_tables = []
        for table in tables_with_company_id:
            if f"`{table}`" in sql or f" {table} " in sql or f" {table}." in sql:
                involved_tables.append(table)

        if not involved_tables:
            self._debug_log(
                'company_filter_skip',
                reason='no_tables_with_company_id',
                original_sql=original_sql
            )
            return sql  # 如果没有涉及包含company_id的表，直接返回

        # 移除SQL末尾的分号（如果有的话）
        sql = sql.rstrip(';').strip()

        table_name = involved_tables[0]
        company_and = f" AND `{table_name}`.`company_id` = {id_val}"
        company_where = f" WHERE `{table_name}`.`company_id` = {id_val}"

        # 计算插入位置：必须在WHERE子句内、并在ORDER/GROUP/HAVING/LIMIT之前
        clause_positions = []
        for clause in [" group by ", " order by ", " having ", " limit "]:
            pos = sql_lower.find(clause)
            if pos != -1:
                clause_positions.append(pos)
        next_clause_pos = min(clause_positions) if clause_positions else len(sql)

        where_idx = sql_lower.find(" where ")
        if where_idx != -1:
            # 已有WHERE：在下一个子句之前插入AND过滤
            insert_pos = next_clause_pos
            sql = sql[:insert_pos] + company_and + sql[insert_pos:]
        else:
            # 没有WHERE：在下一个子句之前插入WHERE过滤
            insert_pos = next_clause_pos
            sql = sql[:insert_pos] + company_where + sql[insert_pos:]

        self._debug_log(
            'company_filter_applied',
            original_sql=original_sql,
            final_sql=sql,
            filter_value=id_val,
            involved_table=involved_tables[0] if involved_tables else None
        )
        return sql
    
    def _get_tables_with_company_id(self):
        """获取包含company_id字段的表列表（动态检测）"""
        if self._tables_with_company_id_cache is not None:
            return self._tables_with_company_id_cache

        tables_with_company_id = []

        if not self._ensure_connection():
            self._tables_with_company_id_cache = tables_with_company_id
            return tables_with_company_id

        conn = None
        cursor = None
        
        try:
            conn = self.connection_pool.get_connection()
            if not conn:
                print("⚠️ 无法从连接池获取连接")
                self._tables_with_company_id_cache = self.get_table_list()
                return self._tables_with_company_id_cache
            
            cursor = conn.cursor()
            tables = self.get_table_list()
            for table in tables:
                try:
                    cursor.execute(f"SHOW COLUMNS FROM `{table}` LIKE 'company_id'")
                    if cursor.fetchone():
                        tables_with_company_id.append(table)
                except Exception:
                    # 某些视图或权限不足的表可能会报错，忽略继续
                    continue
        except Error as e:
            print(f"检测company_id列时出错: {e}")
        finally:
            if cursor:
                try:
                    cursor.close()
                except Exception:
                    pass
            if conn:
                try:
                    conn.close()  # Return to pool
                except Exception:
                    pass

        # 如果没有检测到任何表，退化为全表列表，避免造成过滤缺失
        if not tables_with_company_id:
            tables_with_company_id = self.get_table_list()

        self._tables_with_company_id_cache = tables_with_company_id
        return tables_with_company_id
    
    def _build_company_filter_guidance(self):
        """Return guidance text regarding company filtering for prompts."""
        if self.company_filter_enabled and self.current_company_id is not None:
            return (
                f"8. **重要：所有查询必须限制在company_id = {self.current_company_id}的数据范围内**\n"
                f"   - 如果查询的表包含company_id字段，必须添加WHERE条件：company_id = {self.current_company_id}\n"
                "   - 如果查询涉及多个表，确保所有相关表都添加company_id过滤条件\n"
                "   - 如果表通过外键关联到包含company_id的表，使用JOIN确保数据过滤正确"
            )
        return (
            "8. 如果查询需要区分不同公司，请在SQL中显式处理company_id字段，例如使用WHERE或JOIN条件。"
        )

    # -------------------- Date Filter Injection Utilities --------------------
    
    def _build_memory_summary(self, session_id=None):
        """Build a short conversation memory summary for prompts."""
        try:
            if not session_id:
                return ""
            ctx = self.conversation_manager.get_conversation_details(session_id)
            if not ctx:
                return ""
            parts = []
            if getattr(ctx, 'original_question', None):
                parts.append(f"Original question: {ctx.original_question}")
            if getattr(ctx, 'analysis_result', None):
                brief = str(ctx.analysis_result).strip()
                if len(brief) > 600:
                    brief = brief[:600] + "..."
                parts.append(f"Original analysis summary: {brief}")
            hist = getattr(ctx, 'conversation_history', []) or []
            followups = [h for h in hist if h.get('type') == 'followup']
            recent = followups[-2:]
            for i, h in enumerate(recent, 1):
                q = h.get('question') or ""
                a = h.get('analysis') or h.get('response') or ""
                if len(a) > 300:
                    a = a[:300] + "..."
                parts.append(f"Follow-up {i} Q: {q}")
                parts.append(f"Follow-up {i} A: {a}")
            return "\n".join(parts).strip()
        except Exception:
            return ""

    # -------------------- Aggregate Safety Utilities --------------------
    def _sanitize_aggregates(self, sql: str) -> str:
        """Ensure aggregates are null-safe and reasonable.
        - Replace SUM(x) with SUM(COALESCE(x, 0)) where x looks like a column.
        - Leave SUM(DISTINCT ...) intact.
        """
        if not sql or 'sum(' not in sql.lower():
            return sql
        try:
            def repl_sum(m):
                inner = m.group(1)
                # Skip DISTINCT sums
                if inner.strip().lower().startswith('distinct'):
                    return f"SUM({inner})"
                # If it's already using COALESCE/IFNULL, keep as-is
                inner_l = inner.lower()
                if inner_l.startswith('coalesce(') or inner_l.startswith('ifnull('):
                    return f"SUM({inner})"
                # Only wrap simple column references
                return f"SUM(COALESCE({inner}, 0))"

            # Replace SUM(...) occurrences
            sql = re.sub(r"SUM\s*\(\s*(.*?)\s*\)", repl_sum, sql, flags=re.IGNORECASE)
            return sql
        except Exception:
            return sql

    def _build_date_condition_template(self, question: str) -> str:
        """Return a condition template string with '{col}' placeholder or ''.
        Handles explicit dates, ranges, months, years, quarters, and relative Chinese terms.
        """
        if not question:
            return ""
        q = question.strip()
        q_l = q.lower()

        # Helper: chinese numerals to int
        zh_map = {'一':1, '二':2, '三':3, '四':4, '五':5, '六':6, '七':7, '八':8, '九':9, '十':10}
        def zh_to_int(token: str) -> int | None:
            if token is None:
                return None
            token = token.strip()
            if token.isdigit():
                return int(token)
            # 支持 一 二 三 四 / 十一 十二
            if token in zh_map:
                return zh_map[token]
            if len(token) == 2 and token[0] == '十' and token[1] in zh_map:
                return 10 + zh_map[token[1]]
            if len(token) == 2 and token[1] == '十' and token[0] in zh_map:
                return zh_map[token[0]] * 10
            return None

        # 1) Explicit date range like 2024-01-01 到 2024-02-01 (or Chinese 年月日)
        range_patterns = [
            r"(\d{4}[\-/\.年]\d{1,2}(?:[\-/\.月]\d{1,2}(?:日)?)?)\s*(?:到|至|~|—|–|~|——)\s*(\d{4}[\-/\.年]\d{1,2}(?:[\-/\.月]\d{1,2}(?:日)?)?)",
            r"between\s+(\d{4}[\-/\.](?:\d{1,2})(?:[\-/\.]\d{1,2})?)\s+and\s+(\d{4}[\-/\.](?:\d{1,2})(?:[\-/\.]\d{1,2})?)",
            r"from\s+(\d{4}[\-/\.](?:\d{1,2})(?:[\-/\.]\d{1,2})?)\s+(?:to|through|thru|until|till)\s+(\d{4}[\-/\.](?:\d{1,2})(?:[\-/\.]\d{1,2})?)",
        ]
        def norm_date_token(tok: str) -> tuple[int|None,int|None,int|None]:
            tok = tok.replace('年','-').replace('月','-').replace('日','').replace('.','-').replace('/','-')
            parts = [p for p in tok.split('-') if p]
            try:
                y = int(parts[0])
                m = int(parts[1]) if len(parts) > 1 else None
                d = int(parts[2]) if len(parts) > 2 else None
                return y, m, d
            except Exception:
                return None, None, None
        for pat in range_patterns:
            m = re.search(pat, q, re.IGNORECASE)
            if m:
                d1 = m.group(1)
                d2 = m.group(2)
                y1, m1, day1 = norm_date_token(d1)
                y2, m2, day2 = norm_date_token(d2)
                if y1 and y2:
                    if m1 and day1 and m2 and day2:
                        return f"DATE({{col}}) BETWEEN '{y1:04d}-{m1:02d}-{day1:02d}' AND '{y2:04d}-{m2:02d}-{day2:02d}'"
                    if m1 and not day1 and m2 and not day2:
                        # Month range: use year/month comparison
                        return f"(YEAR({{col}}) * 12 + MONTH({{col}})) BETWEEN ({y1}*12+{m1}) AND ({y2}*12+{m2})"
                    if m1 and day1 and m2 and not day2:
                        return f"DATE({{col}}) >= '{y1:04d}-{m1:02d}-{day1:02d}' AND YEAR({{col}})={y2} AND MONTH({{col}})={m2}"
                    if m1 and not day1 and m2 and day2:
                        return f"YEAR({{col}})={y1} AND MONTH({{col}})={m1} AND DATE({{col}}) <= '{y2:04d}-{m2:02d}-{day2:02d}'"
        
        # 2) Single explicit date yyyy-mm-dd
        m = re.search(r"(20\d{2})[\-/\.](\d{1,2})[\-/\.]((?:0|1|2|3)?\d)", q)
        if m:
            y, mo, da = int(m.group(1)), int(m.group(2)), int(m.group(3))
            return f"DATE({{col}}) = '{y:04d}-{mo:02d}-{da:02d}'"

        # 3) Explicit year-month
        m = re.search(r"(20\d{2})[\-/\.年](\d{1,2})[月]?", q)
        if m:
            y, mo = int(m.group(1)), int(m.group(2))
            return f"YEAR({{col}}) = {y} AND MONTH({{col}}) = {mo}"

        # 4) Explicit year only like 2023年 / 2023
        m = re.search(r"\b(20\d{2})\s*(?:年|年度)?\b", q)
        if m and ('月' not in q and '季度' not in q and 'Q' not in q.upper()):
            y = int(m.group(1))
            return f"YEAR({{col}}) = {y}"

        # 4.1) Chinese month-only (assume current year) like "10月" / "10月份"
        m = re.search(r"(?<!\d)(\d{1,2})\s*月(?:份)?", q)
        if m:
            mo = int(m.group(1))
            if 1 <= mo <= 12:
                return f"YEAR({{col}}) = YEAR(CURDATE()) AND MONTH({{col}}) = {mo}"

        # 4.2) Chinese month-only with Chinese numerals like "十月" / "十一月"
        zh_map = {'一':1, '二':2, '三':3, '四':4, '五':5, '六':6, '七':7, '八':8, '九':9, '十':10}
        m = re.search(r"([一二三四五六七八九十]{1,3})月(?:份)?", q)
        if m:
            token = m.group(1)
            def zh_to_int_local(tok: str) -> int | None:
                tok = tok.strip()
                if tok in zh_map:
                    return zh_map[tok]
                # 十一 / 十二
                if len(tok) == 2 and tok[0] == '十' and tok[1] in zh_map:
                    return 10 + zh_map[tok[1]]
                # 二十 / 三十（仅月份最多到12，不会命中，但留作健壮性）
                if len(tok) == 2 and tok[1] == '十' and tok[0] in zh_map:
                    return zh_map[tok[0]] * 10
                return None
            mo = zh_to_int_local(token) or 0
            if 1 <= mo <= 12:
                return f"YEAR({{col}}) = YEAR(CURDATE()) AND MONTH({{col}}) = {mo}"

        # 5) Quarter expressions
        # e.g., 2024Q3 / 2024年第3季度 / Q2 2023 / 本季度 / 上季度
        if any(k in q for k in ['本季度', '这季度']) or re.search(r"\bthis\s+quarter\b", q_l):
            return "YEAR({col}) = YEAR(CURDATE()) AND QUARTER({col}) = QUARTER(CURDATE())"
        if any(k in q for k in ['上季度', '上一季度']) or re.search(r"\b(last|previous)\s+quarter\b", q_l):
            return "YEAR({col}) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 QUARTER)) AND QUARTER({col}) = QUARTER(DATE_SUB(CURDATE(), INTERVAL 1 QUARTER))"
        m = re.search(r"(20\d{2})\s*年?\s*第?([一二三四1234])\s*季度", q)
        if m:
            y = int(m.group(1))
            qn = zh_to_int(m.group(2)) or int(m.group(2))
            return f"YEAR({{col}}) = {y} AND QUARTER({{col}}) = {qn}"
        m = re.search(r"\b(20\d{2})\s*Q([1-4])\b", q_l, re.IGNORECASE)
        if m:
            y, qn = int(m.group(1)), int(m.group(2))
            return f"YEAR({{col}}) = {y} AND QUARTER({{col}}) = {qn}"
        m = re.search(r"\bQ([1-4])\s*(20\d{2})\b", q_l, re.IGNORECASE)
        if m:
            qn, y = int(m.group(1)), int(m.group(2))
            return f"YEAR({{col}}) = {y} AND QUARTER({{col}}) = {qn}"

        # 6) Half-year
        if '上半年' in q:
            return "YEAR({col}) = YEAR(CURDATE()) AND MONTH({col}) BETWEEN 1 AND 6"
        if '下半年' in q:
            return "YEAR({col}) = YEAR(CURDATE()) AND MONTH({col}) BETWEEN 7 AND 12"

        # 7) Relative periods
        if any(k in q for k in ['今天', '今日', 'today']):
            return "DATE({col}) = CURDATE()"
        if any(k in q for k in ['昨天', '昨日', 'yesterday']):
            return "DATE({col}) = CURDATE() - INTERVAL 1 DAY"
        if any(k in q for k in ['本周', '这周', '当周', 'this week', 'current week']):
            return "YEARWEEK({col}, 1) = YEARWEEK(CURDATE(), 1)"
        if any(k in q for k in ['上周', '上一周', '上星期', '上礼拜', 'last week', 'previous week']):
            return "YEARWEEK({col}, 1) = YEARWEEK(CURDATE() - INTERVAL 1 WEEK, 1)"
        if any(k in q for k in ['本月', '这个月', '当月', 'this month', 'current month']):
            return "YEAR({col}) = YEAR(CURDATE()) AND MONTH({col}) = MONTH(CURDATE())"
        if any(k in q for k in ['上月', '上个月', '上一个月', 'last month', 'previous month']):
            return "YEAR({col}) = YEAR(CURDATE() - INTERVAL 1 MONTH) AND MONTH({col}) = MONTH(CURDATE() - INTERVAL 1 MONTH)"
        if any(k in q for k in ['今年', '本年', '当年', 'this year', 'current year']):
            return "YEAR({col}) = YEAR(CURDATE())"
        if any(k in q for k in ['去年', '上一年', '上年', 'last year', 'previous year']):
            return "YEAR({col}) = YEAR(CURDATE()) - 1"
        # 最近X天/周/月/年
        m = re.search(r"(?:最近|过去|近)\s*(\d+)\s*(天|日|周|星期|月|年)", q)
        if m:
            n = int(m.group(1))
            unit = m.group(2)
            if unit in ['天', '日']:
                return f"DATE({{col}}) >= CURDATE() - INTERVAL {n} DAY"
            if unit in ['周', '星期']:
                return f"DATE({{col}}) >= CURDATE() - INTERVAL {n} WEEK"
            if unit in ['月']:
                return f"DATE({{col}}) >= CURDATE() - INTERVAL {n} MONTH"
            if unit in ['年']:
                return f"DATE({{col}}) >= CURDATE() - INTERVAL {n} YEAR"

        # English: last/past/previous N units, or "in the last N ..."
        m = re.search(r"(?:in\s+the\s+last\s+|last\s+|past\s+|previous\s+)(\d+)\s+(day|days|week|weeks|month|months|year|years)", q_l, re.IGNORECASE)
        if m:
            n = int(m.group(1))
            unit = m.group(2).lower()
            if unit.startswith('day'):
                return f"DATE({{col}}) >= CURDATE() - INTERVAL {n} DAY"
            if unit.startswith('week'):
                return f"DATE({{col}}) >= CURDATE() - INTERVAL {n} WEEK"
            if unit.startswith('month'):
                return f"DATE({{col}}) >= CURDATE() - INTERVAL {n} MONTH"
            if unit.startswith('year'):
                return f"DATE({{col}}) >= CURDATE() - INTERVAL {n} YEAR"

        # English: month names with optional year (Oct 2024, in October) and day (Oct 1, 2024 or 1 Oct 2024)
        month_map = {
            'jan': 1, 'january': 1, 'feb': 2, 'february': 2, 'mar': 3, 'march': 3,
            'apr': 4, 'april': 4, 'may': 5, 'jun': 6, 'june': 6, 'jul': 7, 'july': 7,
            'aug': 8, 'august': 8, 'sep': 9, 'sept': 9, 'september': 9,
            'oct': 10, 'october': 10, 'nov': 11, 'november': 11, 'dec': 12, 'december': 12
        }
        m = re.search(r"\b(jan(?:uary)?|feb(?:ruary)?|mar(?:ch)?|apr(?:il)?|may|jun(?:e)?|jul(?:y)?|aug(?:ust)?|sep(?:t|tember)?|oct(?:ober)?|nov(?:ember)?|dec(?:ember)?)\b\s*,?\s*(20\d{2})", q_l, re.IGNORECASE)
        if m:
            mon, y = m.groups()
            mo = month_map[mon.lower()]
            return f"YEAR({{col}}) = {int(y)} AND MONTH({{col}}) = {mo}"
        m = re.search(r"\b(?:in|during)\s+(jan(?:uary)?|feb(?:ruary)?|mar(?:ch)?|apr(?:il)?|may|jun(?:e)?|jul(?:y)?|aug(?:ust)?|sep(?:t|tember)?|oct(?:ober)?|nov(?:ember)?|dec(?:ember)?)\b", q_l, re.IGNORECASE)
        if m:
            mo = month_map[m.group(1).lower()]
            return f"YEAR({{col}}) = YEAR(CURDATE()) AND MONTH({{col}}) = {mo}"
        m = re.search(r"\b(jan(?:uary)?|feb(?:ruary)?|mar(?:ch)?|apr(?:il)?|may|jun(?:e)?|jul(?:y)?|aug(?:ust)?|sep(?:t|tember)?|oct(?:ober)?|nov(?:ember)?|dec(?:ember)?)\s+(\d{1,2}),?\s*(20\d{2})\b", q_l, re.IGNORECASE)
        if m:
            mon, d, y = m.groups()
            mo = month_map[mon.lower()]
            return f"DATE({{col}}) = '{int(y):04d}-{mo:02d}-{int(d):02d}'"
        m = re.search(r"\b(\d{1,2})\s+(jan(?:uary)?|feb(?:ruary)?|mar(?:ch)?|apr(?:il)?|may|jun(?:e)?|jul(?:y)?|aug(?:ust)?|sep(?:t|tember)?|oct(?:ober)?|nov(?:ember)?|dec(?:ember)?)\s+(20\d{2})\b", q_l, re.IGNORECASE)
        if m:
            d, mon, y = m.groups()
            mo = month_map[mon.lower()]
            return f"DATE({{col}}) = '{int(y):04d}-{mo:02d}-{int(d):02d}'"

        # No timeframe found
        return ""

    def _find_tables_and_aliases(self, sql: str):
        """Return list of (table, alias_or_none) in order of appearance for FROM/JOIN."""
        pattern = re.compile(r"(?:from|join)\s+`?(\w+)`?(?:\s+(?:as\s+)?`?(\w+)`?)?", re.IGNORECASE)
        return [(m.group(1), m.group(2)) for m in pattern.finditer(sql)]

    def _choose_date_table(self, sql: str, tables_aliases: list[tuple[str, str|None]]):
        """Heuristic: prefer domain tables likely driving metrics; otherwise the first table."""
        priority = [
            'invoices','payments','purchase_orders','purchase_bills','purchase_vendor_payments','purchase_vendor_credits',
            'expenses','projects','tasks','attendances','employee_monthly_salaries','employee_payroll_cycles',
            'leaves','assets','proposals','estimates','client_details','deals'
        ]
        found = {tbl: alias for (tbl, alias) in tables_aliases}
        for p in priority:
            if p in found:
                return p, found[p]
        return tables_aliases[0] if tables_aliases else (None, None)

    def _ensure_date_filter(self, sql: str, question: str) -> str:
        """Inject a date filter into SQL based on user's question when applicable.
        - Parses date/timeframe from natural language (Chinese/English hints)
        - Picks a suitable date column for the main table (with alias) via map
        - Inserts condition into WHERE, before GROUP/ORDER/HAVING/LIMIT
        """
        original_sql = sql
        if not sql or not question:
            self._debug_log('date_filter_skip', reason='missing_sql_or_question', original_sql=original_sql)
            return sql
        try:
            cond_tmpl = self._build_date_condition_template(question)
            if not cond_tmpl:
                self._debug_log('date_filter_skip', reason='no_timeframe_detected', original_sql=original_sql)
                return sql

            sql_no_semicolon = sql.rstrip(';').strip()
            sql_lower = sql_no_semicolon.lower()

            # Skip complex UNION queries to avoid breaking structure
            if ' union ' in sql_lower:
                self._debug_log('date_filter_skip', reason='union_query', original_sql=original_sql)
                return sql

            # Extract tables and aliases
            tables_aliases = self._find_tables_and_aliases(sql_no_semicolon)
            if not tables_aliases:
                self._debug_log('date_filter_skip', reason='no_tables_detected', original_sql=original_sql)
                return sql
            table, alias = self._choose_date_table(sql_no_semicolon, tables_aliases)
            if not table:
                self._debug_log('date_filter_skip', reason='no_priority_table', original_sql=original_sql)
                return sql
            date_col = self._date_column_map.get(table, 'created_at')
            qual = alias or table
            col_expr = f"`{qual}`.`{date_col}`"

            # Check if WHERE already contains a filter on this date column
            # Only inspect the WHERE segment
            where_match = re.search(r'\bwhere\b', sql_lower)
            where_idx = where_match.start() if where_match else -1
            end_pos = len(sql_no_semicolon)
            clause_patterns = [
                r'\bgroup\s+by\b',
                r'\border\s+by\b',
                r'\bhaving\b',
                r'\blimit\b'
            ]
            for pat in clause_patterns:
                m = re.search(pat, sql_lower)
                if m:
                    end_pos = min(end_pos, m.start())
            where_segment = sql_no_semicolon[where_idx:end_pos] if where_idx != -1 else ''
            if where_segment:
                # If WHERE already filters on the chosen date column, skip
                if re.search(r"\b(" + re.escape(qual) + r"\.)?`?" + re.escape(date_col) + r"`?\b", where_segment, re.IGNORECASE):
                    self._debug_log('date_filter_skip', reason='existing_column_filter', original_sql=original_sql, table=table, date_column=date_col)
                    return sql
                # If WHERE has any generic date expressions, skip to avoid double-filtering
                generic_date_patterns = [
                    r"\bdate\s*\(", r"\byear\s*\(", r"\bmonth\s*\(", r"\bquarter\s*\(",
                    r"\bcurdate\s*\(", r"\bnow\s*\(",
                    # quoted or unquoted range expressions
                    r"\bbetween\s*'20\d{2}",
                    r"\bbetween\s+20\d{2}[-/.]\d{1,2}[-/.]\d{1,2}\s+and\s+20\d{2}[-/.]\d{1,2}[-/.]\d{1,2}",
                    r"\bfrom\s+20\d{2}[-/.]\d{1,2}[-/.]\d{1,2}\s+(?:to|through|thru|until|till)\s+20\d{2}[-/.]\d{1,2}[-/.]\d{1,2}",
                    r"'20\d{2}[-/.]\d{1,2}(?:[-/.]\d{1,2})?'"
                ]
                for gp in generic_date_patterns:
                    if re.search(gp, where_segment, re.IGNORECASE):
                        self._debug_log('date_filter_skip', reason='existing_generic_date_filter', original_sql=original_sql, table=table)
                        return sql

            # Build date condition
            cond = cond_tmpl.format(col=col_expr)
            # Determine insertion point
            clause_positions = []
            for clause in [' group by ', ' order by ', ' having ', ' limit ']:
                pos = sql_lower.find(clause)
                if pos != -1:
                    clause_positions.append(pos)
            next_clause_pos = min(clause_positions) if clause_positions else len(sql_no_semicolon)

            if where_idx != -1:
                insert_pos = next_clause_pos
                updated = sql_no_semicolon[:insert_pos] + f" AND (" + cond + ")" + sql_no_semicolon[insert_pos:]
            else:
                insert_pos = next_clause_pos
                updated = sql_no_semicolon[:insert_pos] + f" WHERE " + cond + sql_no_semicolon[insert_pos:]

            final_sql = updated + (';' if sql.endswith(';') else '')
            self._debug_log(
                'date_filter_applied',
                original_sql=original_sql,
                final_sql=final_sql,
                question=question,
                chosen_table=table,
                date_column=date_col,
                condition=cond
            )
            return final_sql
        except Exception as exc:
            self._debug_log('date_filter_skip', reason='exception', error=str(exc), original_sql=original_sql)
            return sql
    
    def _get_thread_connection(self):
        """Get a connection from the pool for thread-safe execution"""
        try:
            if not self.connection_pool:
                print("⚠️ 连接池未初始化，正在创建...")
                if not self.connect_to_database():
                    return None
            
            # Get connection from pool
            conn = self.connection_pool.get_connection()
            if conn.is_connected():
                return conn
            else:
                conn.close()
                return None
        except Exception as e:
            print(f"❌ 从连接池获取连接失败: {e}")
            return None
    
    def _validate_sql_before_execution(self, sql: str) -> tuple[bool, str]:
        """Validate SQL for common errors before execution to fail fast"""
        sql_lower = sql.lower()
        errors = []
        
        # Check for non-existent columns in deals table
        if 'deals' in sql_lower or '`deals`' in sql_lower:
            if re.search(r'\bd\.client_id\b', sql_lower) or re.search(r'`d`\.`client_id`', sql_lower):
                errors.append("deals表没有client_id列！deals表通过lead_id关联leads表，leads表有client_id")
            if re.search(r'\bd\.product_id\b', sql_lower) or re.search(r'`d`\.`product_id`', sql_lower):
                errors.append("deals表没有product_id列！deals表不直接关联products表")
            if re.search(r'\bd\.amount\b', sql_lower) or re.search(r'`d`\.`amount`', sql_lower):
                errors.append("deals表没有amount列！应该使用value列（deals表主要字段：id, name, value, close_date, next_follow_up, lead_id, agent_id, created_at, updated_at）")
        
        # Check for non-existent tables
        if re.search(r'\bproduct_reviews\b', sql_lower) or re.search(r'`product_reviews`', sql_lower):
            errors.append("product_reviews表不存在！请使用其他表进行产品相关查询")
        
        # Check for wrong table name: clients (should be client_details)
        if re.search(r'\bclients\b', sql_lower) or re.search(r'`clients`', sql_lower):
            # Check if it's not client_details or client_categories
            if 'client_details' not in sql_lower and 'client_categories' not in sql_lower:
                errors.append("clients表不存在！应该使用client_details表")
        
        # Check for wrong column names in invoices table
        if 'invoices' in sql_lower or '`invoices`' in sql_lower:
            if re.search(r'\binvoice_date\b', sql_lower) or re.search(r'`invoice_date`', sql_lower):
                errors.append("invoices表没有invoice_date列！应该使用issue_date列")
        
        # Check for wrong column names in expenses table
        if 'expenses' in sql_lower or '`expenses`' in sql_lower:
            if re.search(r'\bpayment_date\b', sql_lower) or re.search(r'`payment_date`', sql_lower):
                errors.append("expenses表没有payment_date列！应该使用purchase_date列")
            if re.search(r'\bbalance\b', sql_lower) or re.search(r'`balance`', sql_lower):
                errors.append("expenses表没有balance列！请检查实际列名")
        
        # Check for non-existent expense_categories table
        if re.search(r'\bexpense_categories\b', sql_lower) or re.search(r'`expense_categories`', sql_lower):
            errors.append("expense_categories表不存在！expenses表可能使用category_id列直接关联其他表")
        
        # Check for wrong column names in payments table
        if 'payments' in sql_lower or '`payments`' in sql_lower:
            if re.search(r'\bpayment_date\b', sql_lower) or re.search(r'`payment_date`', sql_lower):
                # Check if it's actually payments table (not invoice_payment_details)
                if 'invoice_payment_details' not in sql_lower:
                    errors.append("payments表没有payment_date列！应该使用paid_on列")
        
        if errors:
            return False, " | ".join(errors)
        
        return True, ""
    
    def execute_sql(self, sql, use_thread_connection=False):
        """Execute SQL query and return results as DataFrame
        
        Args:
            sql: SQL query string
            use_thread_connection: If True, create a new connection for this query (for parallel execution)
        """
        # Fast validation before execution
        is_valid, error_msg = self._validate_sql_before_execution(sql)
        if not is_valid:
            error_detail = f"❌ SQL验证失败: {error_msg}"
            print(error_detail)
            print(f"❌ 问题SQL: {sql[:200]}...")
            self.logger.error(f"SQL validation failed: {error_msg} - SQL: {sql[:500]}")
            # Return empty DataFrame instead of None so analysis can report the error
            return pd.DataFrame()
        
        conn = None
        try:
            if use_thread_connection:
                # For parallel execution, use a separate connection per thread
                conn = self._get_thread_connection()
                if not conn:
                    print("❌ 无法创建线程连接")
                    return None
            else:
                # For sequential execution, get connection from pool
                if not self._ensure_connection():
                    print("❌ 无法连接到数据库")
                    return None
                conn = self.connection_pool.get_connection()
                if not conn:
                    print("❌ 无法从连接池获取连接")
                    return None
            
            # 使用pandas读取SQL结果
            df = pd.read_sql(sql, conn)
            
            # Log query execution results
            row_count = len(df) if df is not None else 0
            self.logger.info(f"SQL executed successfully: {row_count} rows returned")
            
            if row_count == 0:
                self.logger.warning(f"SQL query returned 0 rows: {sql[:200]}...")
                print(f"⚠️ 警告: SQL查询返回0行数据")
            
            return df
        except mysql.connector.Error as e:
            error_msg = str(e)
            print(f"❌ 执行SQL时出错 (MySQL错误): {e}")
            
            # Provide helpful error messages for common issues
            if "Unknown column" in error_msg:
                sql_lower = sql.lower()
                if "client_id" in error_msg and "deals" in sql_lower:
                    print("💡 提示: deals表没有client_id列，请通过lead_id关联leads表")
                elif "product_id" in error_msg and "deals" in sql_lower:
                    print("💡 提示: deals表没有product_id列，deals表不直接关联products表")
                elif "amount" in error_msg and "deals" in sql_lower:
                    print("💡 提示: deals表没有amount列！应该使用value列（deals表主要字段：id, name, value, close_date, next_follow_up, lead_id, agent_id, created_at, updated_at）")
                    self.logger.error(f"Wrong column name: deals.amount should be deals.value - SQL: {sql[:500]}")
                elif "invoice_date" in error_msg:
                    print("💡 提示: invoices表没有invoice_date列！应该使用issue_date列")
                    self.logger.error(f"Wrong column name: invoice_date should be issue_date - SQL: {sql[:500]}")
                elif "payment_date" in error_msg:
                    if "expenses" in sql_lower:
                        print("💡 提示: expenses表没有payment_date列！应该使用purchase_date列")
                        self.logger.error(f"Wrong column name: expenses.payment_date should be purchase_date - SQL: {sql[:500]}")
                    elif "payments" in sql_lower and "invoice_payment_details" not in sql_lower:
                        print("💡 提示: payments表没有payment_date列！应该使用paid_on列")
                        self.logger.error(f"Wrong column name: payments.payment_date should be paid_on - SQL: {sql[:500]}")
                elif "balance" in error_msg and "expenses" in sql_lower:
                    print("💡 提示: expenses表没有balance列！请检查实际列名")
                    self.logger.error(f"Wrong column name: expenses.balance doesn't exist - SQL: {sql[:500]}")
            
            if "doesn't exist" in error_msg:
                if "product_reviews" in error_msg:
                    print("💡 提示: product_reviews表不存在，请使用其他表进行产品相关查询")
                elif "expense_categories" in error_msg:
                    print("💡 提示: expense_categories表不存在！expenses表可能使用category_id列直接关联其他表")
                    self.logger.error(f"Table doesn't exist: expense_categories - SQL: {sql[:500]}")
                elif "clients" in error_msg and "client_details" not in sql.lower() and "client_categories" not in sql.lower():
                    print("💡 提示: clients表不存在！应该使用client_details表")
                    self.logger.error(f"Table doesn't exist: clients should be client_details - SQL: {sql[:500]}")
            
            # If it's a connection error and not using thread connection, try to reconnect
            if not use_thread_connection and e.errno in (2006, 2013):  # MySQL server has gone away, Lost connection
                print("⚠️ 检测到连接错误，尝试重新连接...")
                if self._ensure_connection():
                    try:
                        retry_conn = self.connection_pool.get_connection()
                        df = pd.read_sql(sql, retry_conn)
                        retry_conn.close()  # Return to pool
                        print("✅ 重连后查询成功")
                        return df
                    except Exception as retry_error:
                        print(f"❌ 重连后查询仍然失败: {retry_error}")
                        return None
            return None
        except Exception as e:
            print(f"❌ 执行SQL时出错: {e}")
            import traceback
            traceback.print_exc()
            return None
        finally:
            # Close connection if we got one from pool
            if conn:
                try:
                    conn.close()  # Return to pool
                except Exception:
                    pass
    
    def detect_language(self, text):
        """Detect if text contains Chinese characters"""
        chinese_chars = sum(1 for char in text if '\\u4e00' <= char <= '\\u9fff')
        return 'zh' if chinese_chars > len(text) * 0.1 else 'en'
    
    def analyze_results(self, question, df):
        """Analyze query results using AI"""
        # 检测语言
        language = self.detect_language(question)
        
        if df is None or df.empty:
            if language == 'zh':
                return "没有找到相关数据。"
            else:
                return "No relevant data found."
        
        # 准备数据摘要
        data_summary = f"数据行数: {len(df)}\\n"
        data_summary += f"数据列: {', '.join(df.columns)}\\n"
        
        # 添加数据样本
        if len(df) > 0:
            data_summary += f"\\n前几行数据:\\n"
            for i, (idx, row) in enumerate(df.head().iterrows()):
                row_data = []
                for col in df.columns:
                    row_data.append(f"{col}: {row[col]}")
                data_summary += f"第{i+1}行 - {', '.join(row_data)}\\n"
        
        # 添加数值列的统计信息
        numeric_cols = df.select_dtypes(include=['number']).columns
        if len(numeric_cols) > 0:
            data_summary += f"\\n数值统计:\\n"
            desc = df[numeric_cols].describe()
            for col in desc.columns:
                data_summary += f"{col}列统计:\\n"
                for stat in desc.index:
                    data_summary += f"  {stat}: {desc.loc[stat, col]}\\n"
        
        # 根据语言选择提示词
        if language == 'zh':
            system_prompt = """你是一个专业的数据分析师。请基于提供的查询结果，用中文给出详细的分析和洞察。

分析要求:
1. 总结主要发现
2. 识别重要趋势和模式
3. 提供业务洞察
4. 如果有异常数据，请指出
5. 给出可行的建议

请用清晰、专业的中文回答，直接分析数据内容，无需提及数据来源或查询范围。"""
        else:
            system_prompt = """You are a professional data analyst. Please provide detailed analysis and insights based on the query results.

Analysis requirements:
1. Summarize key findings
2. Identify important trends and patterns
3. Provide business insights
4. Point out any anomalies if present
5. Give actionable recommendations

Please respond in clear, professional English. Focus on the data content directly without mentioning data sources or query scope."""
        
        # 根据语言构建用户提示
        if language == 'zh':
            user_prompt = f"用户问题: {question}\\n\\n查询结果:\\n{data_summary}"
        else:
            user_prompt = f"User Question: {question}\\n\\nQuery Results:\\n{data_summary}"
        
        try:
            response = self.client.chat.completions.create(
                model=self.model,
                messages=[
                    {"role": "system", "content": system_prompt},
                    {"role": "user", "content": user_prompt}
                ],
                temperature=0.3,
                max_tokens=2000
            )
            
            return response.choices[0].message.content
            
        except Exception as e:
            if language == 'zh':
                return f"分析数据时出错: {e}"
            else:
                return f"Error analyzing data: {e}"
    
    def analyze_data(self, data, question, mode=None):
        """Complete data analysis workflow"""
        print(f"\\n🤖 正在分析问题: {question}")
        
        # 生成SQL
        print("📝 生成SQL查询...")
        sql = self.generate_sql(question, mode)
        print(f"SQL: {sql}")
        
        # 执行SQL
        print("🔍 执行查询...")
        df = self.execute_sql(sql)
        
        if df is None:
            return {
                'question': question,
                'sql': sql,
                'error': '查询执行失败',
                'analysis': '无法获取数据进行分析'
            }
        
        # 分析结果
        print("📊 分析结果...")
        analysis = self.analyze_results(question, df)
        
        result = {
            'question': question,
            'sql': sql,
            'data': df,
            'analysis': analysis,
            'timestamp': datetime.now().isoformat()
        }
        
        return result
    
    def generate_sql_multi(self, question, mode=None, session_id=None):
        """Generate one or more SQL statements for a compound question.

        Returns a list of dicts: [{"name": str, "purpose": str, "sql": str}].
        Falls back to a single-item list if only one SQL is appropriate.
        """
        import time
        start_time = time.time()
        try:
            model_to_use = self.fast_model if (mode == 'Flash' and getattr(self, 'fast_model', None)) else self.model
            
            # OPTIMIZATION: Use concise schema info instead of full schema
            # Full schema is 10k+ tokens, concise is ~2k tokens = 3-5x faster LLM response
            tables = self.get_table_list()
            table_count = len(tables)
            
            # 获取精简的schema概要（控制Token体积）
            schema_preview = self.get_schema_summary(max_chars=2800)
            
            prompt = f"""
You are a SQL expert. Generate ONE OR MORE SQL queries to answer the user's question. Split into separate queries for different metrics (counts, breakdowns, trends, top-N).

**Database: {table_count} tables available. All tables accessible.**

**CRITICAL WARNINGS - DO NOT USE:**
- ❌ deals表没有client_id列！deals表通过lead_id关联leads表，leads表有client_id
- ❌ deals表没有product_id列！deals表不直接关联products表
- ❌ deals表没有amount列！应该使用value列（deals表主要字段：id, name, value, close_date, next_follow_up, lead_id, agent_id, created_at, updated_at）
- ❌ product_reviews表不存在！不要使用此表
- ❌ expense_categories表不存在！expenses表可能使用category_id列直接关联其他表
- ❌ clients表不存在！应该使用client_details表

**CRITICAL COLUMN NAME CORRECTIONS:**
- ✅ invoices表使用issue_date列（不是invoice_date！）
- ✅ expenses表使用purchase_date列（不是payment_date！）
- ✅ payments表使用paid_on列（不是payment_date！）
- ✅ expenses表没有balance列
- ✅ deals表使用value列（不是amount！deals表主要字段：id, name, value, close_date, next_follow_up, lead_id, agent_id, created_at, updated_at）

Key schema info:
{schema_preview}

User question: {question}

Requirements:
- Use valid MySQL syntax with backticks for table/column names.
- Split into separate queries for different metrics.
- IMPORTANT: Do NOT add LIMIT unless user requests "top N" or "first N". Return ALL data by default.
- **CRITICAL: Verify table/column existence before using them. Do NOT use deals.client_id, deals.product_id, deals.amount, or product_reviews table. Use deals.value instead of deals.amount.**
- Output STRICT JSON only:
  {{
    "queries": [
      {{"name": "...", "purpose": "...", "sql": "..."}},
      {{"name": "...", "purpose": "...", "sql": "..."}}
    ]
  }}
- No markdown, no code fences, ASCII-only.
"""
            _mem2 = self._build_memory_summary(session_id) if session_id else ""
            if _mem2:
                prompt = prompt.replace("User question:", f"Conversation memory (if relevant):\n{_mem2}\n\nUser question:")

            if mode == 'Flash':
                prompt += "\nFLASH MODE: You may return complete queries without restrictions."

            # OPTIMIZATION: Limit max_tokens for faster response
            response = self.client.chat.completions.create(
                model=model_to_use,
                messages=[{"role": "user", "content": prompt}],
                temperature=0.0,
                max_tokens=2000  # Limit response size for speed
            )
            
            elapsed = time.time() - start_time
            print(f"⚡ [generate_sql_multi] LLM call completed in {elapsed:.2f}s")

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
                import json
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
                        # Post-process: ensure company/date filters
                        processed = []
                        for item in normalized:
                            s = item["sql"].strip().rstrip(';')
                            s = self._ensure_company_filter(s)
                            s = self._ensure_date_filter(s, question)
                            if not s.endswith(';'):
                                s = s + ';'
                            processed.append({"name": item["name"], "purpose": item.get("purpose") or "", "sql": s})
                        print(f"Generated SQL list (JSON): {[n['sql'] for n in processed]}")
                        return processed
            except Exception:
                # Not JSON; continue to regex extraction
                pass

            # Fallback: extract multiple SQLs from raw text
            extracted = _extract_sqls_from_text(content)
            if extracted:
                # Post-process: ensure company/date filters
                processed = []
                for item in extracted:
                    s = item["sql"].strip().rstrip(';')
                    s = self._ensure_company_filter(s)
                    s = self._ensure_date_filter(s, question)
                    if not s.endswith(';'):
                        s += ';'
                    processed.append({"name": item["name"], "purpose": item.get("purpose") or "", "sql": s})
                print(f"Generated SQL list (fallback): {[n['sql'] for n in processed]}")
                return processed

            # Final fallback: use single SQL generator
            single_sql = self.generate_sql(question, mode=mode, session_id=session_id)
            if single_sql:
                return [{"name": "Query 1", "purpose": "Single query", "sql": single_sql if single_sql.endswith(';') else single_sql + ';'}]
            return []
        except Exception as e:
            print(f"Error generating multi SQL: {e}")
            return []

    def _build_comprehensive_data_summary(self, df: pd.DataFrame, language: str = 'english') -> str:
        """Build comprehensive data summary with full statistics for DataReports mode.
        
        Includes:
        - Total row count
        - Column statistics (sum, count, avg, min, max for numeric columns)
        - Unique counts for text columns
        - Full data if dataset is small (< 100 rows)
        """
        try:
            if df is None or df.empty:
                return "Dataset is empty - no data available."
            
            lines = []
            if language == 'zh':
                lines.append(f"=== 数据集统计信息 ===")
                lines.append(f"总行数: {len(df)}")
                lines.append(f"总列数: {len(df.columns)}")
                lines.append(f"\n列名: {', '.join(df.columns.tolist())}")
            else:
                lines.append(f"=== Dataset Statistics ===")
                lines.append(f"Total Rows: {len(df)}")
                lines.append(f"Total Columns: {len(df.columns)}")
                lines.append(f"\nColumn Names: {', '.join(df.columns.tolist())}")
            
            # Calculate approximate token count (rough estimate: 1 token ≈ 4 characters)
            # For DataReports, we want to send as much data as possible while staying under ~50k tokens
            # (LLM context window is typically 128k tokens, but we need room for prompt + response)
            MAX_TOKENS_FOR_DATA = 50000  # Conservative limit
            APPROX_CHARS_PER_ROW = sum(len(str(col)) for col in df.columns) + 50  # Rough estimate
            
            # If dataset is small enough, include ALL rows
            # For larger datasets, we'll still include comprehensive statistics + more samples
            if len(df) <= 500:  # Increased from 100 to 500 - send all rows for datasets up to 500 rows
                if language == 'zh':
                    lines.append(f"\n=== 完整数据（共{len(df)}行）===")
                else:
                    lines.append(f"\n=== Complete Data ({len(df)} rows) ===")
                for idx, (_, row) in enumerate(df.iterrows(), 1):
                    row_data = []
                    for col in df.columns:
                        val = row[col]
                        # Format values nicely
                        if pd.isna(val):
                            val_str = "NULL"
                        elif isinstance(val, (int, float)):
                            val_str = str(val)
                        else:
                            val_str = str(val)
                        row_data.append(f"{col}={val_str}")
                    lines.append(f"Row {idx}: {', '.join(row_data)}")
            else:
                # For large datasets, include comprehensive statistics
                if language == 'zh':
                    lines.append(f"\n=== 数据统计摘要 ===")
                else:
                    lines.append(f"\n=== Statistical Summary ===")
                
                # Numeric columns statistics
                numeric_cols = df.select_dtypes(include=[np.number]).columns.tolist()
                if numeric_cols:
                    if language == 'zh':
                        lines.append(f"\n数值列统计:")
                    else:
                        lines.append(f"\nNumeric Columns Statistics:")
                    
                    for col in numeric_cols:
                        col_data = df[col].dropna()
                        if len(col_data) > 0:
                            col_sum = col_data.sum()
                            col_count = len(col_data)
                            col_avg = col_data.mean()
                            col_min = col_data.min()
                            col_max = col_data.max()
                            if language == 'zh':
                                lines.append(f"  {col}:")
                                lines.append(f"    总和(SUM): {col_sum}")
                                lines.append(f"    计数(COUNT): {col_count}")
                                lines.append(f"    平均值(AVG): {col_avg:.2f}")
                                lines.append(f"    最小值(MIN): {col_min}")
                                lines.append(f"    最大值(MAX): {col_max}")
                            else:
                                lines.append(f"  {col}:")
                                lines.append(f"    SUM: {col_sum}")
                                lines.append(f"    COUNT: {col_count}")
                                lines.append(f"    AVG: {col_avg:.2f}")
                                lines.append(f"    MIN: {col_min}")
                                lines.append(f"    MAX: {col_max}")
                
                # Text/Categorical columns statistics
                text_cols = df.select_dtypes(include=['object', 'string']).columns.tolist()
                if text_cols:
                    if language == 'zh':
                        lines.append(f"\n文本列统计:")
                    else:
                        lines.append(f"\nText Columns Statistics:")
                    
                    for col in text_cols:
                        col_data = df[col].dropna()
                        if len(col_data) > 0:
                            unique_count = col_data.nunique()
                            if language == 'zh':
                                lines.append(f"  {col}:")
                                lines.append(f"    唯一值数量: {unique_count}")
                                lines.append(f"    非空值数量: {len(col_data)}")
                                # Show top 5 most common values
                                top_values = col_data.value_counts().head(5)
                                lines.append(f"    最常见的值:")
                                for val, count in top_values.items():
                                    lines.append(f"      '{val}': {count}次")
                            else:
                                lines.append(f"  {col}:")
                                lines.append(f"    Unique Values: {unique_count}")
                                lines.append(f"    Non-Null Count: {len(col_data)}")
                                # Show top 5 most common values
                                top_values = col_data.value_counts().head(5)
                                lines.append(f"    Most Common Values:")
                                for val, count in top_values.items():
                                    lines.append(f"      '{val}': {count} times")
                
                # Sample rows - include more samples for better LLM understanding
                # Calculate how many rows we can safely include based on token limits
                estimated_chars_so_far = len("\n".join(lines))
                remaining_chars = (MAX_TOKENS_FOR_DATA * 4) - estimated_chars_so_far  # 4 chars per token
                max_sample_rows = min(200, int(remaining_chars / APPROX_CHARS_PER_ROW))  # Up to 200 rows, or what fits
                
                sample_size = min(50, max_sample_rows // 2)  # Show first N and last N rows
                
                if language == 'zh':
                    lines.append(f"\n=== 数据样本（前{sample_size}行和后{sample_size}行，共{len(df)}行）===")
                else:
                    lines.append(f"\n=== Data Samples (First {sample_size} and Last {sample_size} rows, Total: {len(df)} rows) ===")
                
                # First N rows
                for idx, (_, row) in enumerate(df.head(sample_size).iterrows(), 1):
                    row_data = []
                    for col in df.columns:
                        val = row[col]
                        if pd.isna(val):
                            val_str = "NULL"
                        else:
                            val_str = str(val)
                        row_data.append(f"{col}={val_str}")
                    lines.append(f"Row {idx}: {', '.join(row_data)}")
                
                if len(df) > sample_size * 2:
                    omitted = len(df) - (sample_size * 2)
                    if language == 'zh':
                        lines.append(f"\n... (省略中间 {omitted} 行) ...\n")
                    else:
                        lines.append(f"\n... (omitting middle {omitted} rows) ...\n")
                    
                    # Last N rows
                    for idx, (_, row) in enumerate(df.tail(sample_size).iterrows(), len(df) - sample_size + 1):
                        row_data = []
                        for col in df.columns:
                            val = row[col]
                            if pd.isna(val):
                                val_str = "NULL"
                            else:
                                val_str = str(val)
                            row_data.append(f"{col}={val_str}")
                        lines.append(f"Row {idx}: {', '.join(row_data)}")
                elif len(df) > sample_size:
                    # If dataset is between sample_size and sample_size*2, show remaining rows
                    remaining_start = sample_size + 1
                    for idx, (_, row) in enumerate(df.iloc[sample_size:].iterrows(), remaining_start):
                        row_data = []
                        for col in df.columns:
                            val = row[col]
                            if pd.isna(val):
                                val_str = "NULL"
                            else:
                                val_str = str(val)
                            row_data.append(f"{col}={val_str}")
                        lines.append(f"Row {idx}: {', '.join(row_data)}")
            
            return "\n".join(lines)
        except Exception as e:
            print(f"Error building comprehensive data summary: {e}")
            traceback.print_exc()
            # Fallback to simple summary
            return f"Dataset shape: {df.shape}\nColumns: {', '.join(df.columns.tolist())}\nError building detailed summary: {str(e)}"

    def analyze_multi_data(self, datasets, question, mode=None, session_id=None, stream=False):
        """Analyze multiple query result sets together.

        datasets: List[{"name": str, "sql": str, "df": pd.DataFrame}]
        Returns combined analysis text.
        """
        try:
            # Check if this is a table count meta-question
            # Remove [Flash], [DataReports], etc. prefixes for detection
            question_clean = re.sub(r'^\[.*?\]\s*', '', question, flags=re.IGNORECASE)
            question_lower = question_clean.lower()
            
            # Debug logging
            print(f"🔍 [analyze_multi_data] Question: '{question}'")
            print(f"🔍 [analyze_multi_data] Cleaned: '{question_clean}'")
            print(f"🔍 [analyze_multi_data] Lower: '{question_lower}'")
            
            is_table_count_question = any(keyword in question_lower for keyword in [
                'how many tables', 'how many table', 'how many table can', 'how many table do',
                'table count', 'number of tables', 'total tables', 'accessible tables',
                'can access', 'can you access', 'available tables', 'tables available',
                'tables can you', 'tables do you', 'tables are available', 'tables are accessible',
                'how many tables can', 'how many tables do', 'how many tables are'
            ])
            
            print(f"🔍 [analyze_multi_data] Is table count question: {is_table_count_question}")
            
            # If it's a table count question, get actual count and respond directly
            if is_table_count_question:
                print("✅ [analyze_multi_data] Detected table count question - returning direct response")
                tables = self.get_table_list()
                table_count = len(tables)
                language = self.detect_language(question)
                
                if language == 'zh':
                    response = f"数据库中共有 **{table_count} 个表**，系统可以访问所有表。\n\n"
                    response += f"这些表包括（前20个示例）：\n"
                    for i, table in enumerate(tables[:20], 1):
                        response += f"{i}. {table}\n"
                    if len(tables) > 20:
                        response += f"\n... 以及其他 {len(tables) - 20} 个表。"
                    response += f"\n\n**总计：{table_count} 个表全部可用。**"
                else:
                    response = f"The database contains **{table_count} tables** in total. The system can access all of them.\n\n"
                    response += f"These tables include (first 20 examples):\n"
                    for i, table in enumerate(tables[:20], 1):
                        response += f"{i}. {table}\n"
                    if len(tables) > 20:
                        response += f"\n... and {len(tables) - 20} more tables."
                    response += f"\n\n**Total: All {table_count} tables are accessible.**"
                
                if stream:
                    # Yield response in chunks for streaming
                    chunk_size = 50
                    for i in range(0, len(response), chunk_size):
                        yield response[i:i+chunk_size]
                else:
                    return response
            
            model_to_use = self.fast_model if (mode == 'Flash' and getattr(self, 'fast_model', None)) else self.model
            language = self.detect_language(question)

            # Build per-dataset summaries
            sections = []
            empty_datasets = []
            for idx, item in enumerate(datasets, 1):
                df = item.get("df")
                name = item.get("name") or f"Dataset {idx}"
                sql = item.get("sql") or ""
                if df is None:
                    summary = "No data returned."
                    empty_datasets.append({"name": name, "sql": sql, "reason": "df is None"})
                    self.logger.warning(f"Dataset {idx} ({name}): No data returned (df is None)")
                elif getattr(df, 'empty', True):
                    summary = "Query executed successfully, but no rows returned."
                    empty_datasets.append({"name": name, "sql": sql, "reason": "empty DataFrame"})
                    self.logger.warning(f"Dataset {idx} ({name}): Query returned 0 rows - SQL: {sql[:200]}...")
                    print(f"⚠️ 警告: 数据集 '{name}' 返回0行数据")
                else:
                    # For DataReports mode, include FULL statistics, not just samples
                    if mode == 'DataReports':
                        summary = self._build_comprehensive_data_summary(df, language)
                    else:
                        # For Flash/Charts mode, show sample rows
                        sample_rows = 5 if mode == 'Flash' else 10
                        summary = (
                            f"数据形状：{df.shape}\n列名：{list(df.columns)}\n\n前{sample_rows}行数据：\n"
                        )
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
                if language == 'zh':
                    system_prompt = "你是一个数据查询助手。请直接将各个查询的结果分别呈现，并在最后给出简短的总体描述。专注于数据内容本身，无需提及数据来源或查询范围。"
                else:
                    system_prompt = "You are a data query assistant. Present each query's results clearly and end with a brief overall description. Focus on the data content itself without mentioning data sources or query scope."
            elif mode == 'Charts':
                if language == 'zh':
                    system_prompt = "你是一个数据分析助手。请简要说明数据的基本情况，包括数据量、主要字段和关键数值。保持回复简洁，不超过3-4句话。专注于数据内容，无需提及查询范围。"
                else:
                    system_prompt = "You are a data analysis assistant. Briefly describe the basic data situation, including data volume, main fields, and key values. Keep the response concise, no more than 3-4 sentences. Focus on data content without mentioning query scope."
            else:
                if language == 'zh':
                    system_prompt = """你是一位资深的商业数据专家。请综合分析多个数据集，给出专业洞察与建议。

CRITICAL REQUIREMENTS:
1. 你必须使用数据集中的实际数字和统计数据，不能使用占位符或假设值
2. 所有报告中的数字（如总收入、客户数量、交易次数等）必须来自提供的数据集
3. 如果数据集中显示某个指标为0或空，请明确说明"根据查询结果，该指标为0"或"未找到相关数据"
4. 不要生成通用的报告模板，必须基于实际数据进行分析
5. 如果数据集为空或没有数据，请明确说明"查询未返回任何数据"而不是生成假设的报告

请专注于数据分析和业务价值，使用数据集中的实际数字。"""
                else:
                    system_prompt = """You are a senior business data expert. Synthesize insights across multiple datasets and provide professional recommendations.

CRITICAL REQUIREMENTS:
1. You MUST use actual numbers and statistics from the datasets provided - NO placeholders or assumed values
2. All numbers in your report (revenue, customer count, transaction count, etc.) MUST come from the provided datasets
3. If a dataset shows 0 or empty for a metric, explicitly state "According to the query results, this metric is 0" or "No data found"
4. Do NOT generate generic report templates - analysis MUST be based on actual data
5. If datasets are empty or contain no data, explicitly state "Query returned no data" rather than generating hypothetical reports

Focus on data analysis and business value using ACTUAL numbers from the datasets."""

            # Build user prompt with structured sections
            lines = []
            memory_context = self._build_memory_summary(session_id) if session_id else ""
            if memory_context:
                if language == 'zh':
                    lines.append('会话记忆（供参考）：')
                else:
                    lines.append('Conversation Memory (for reference):')
                lines.append(memory_context)
                if language == 'zh':
                    lines.append(f"用户问题：{question}")
                    lines.append("\n数据集概览：")
                else:
                    lines.append(f"User Question: {question}")
                    lines.append("\nDatasets Overview:")
            else:
                if language == 'zh':
                    lines.append(f"用户问题：{question}")
                    lines.append("\n数据集概览：")
                else:
                    lines.append(f"User Question: {question}")
                    lines.append("\nDatasets Overview:")
            # Log empty datasets summary
            if empty_datasets:
                self.logger.warning(f"DataReports mode: {len(empty_datasets)}/{len(datasets)} datasets returned empty results")
                print(f"⚠️ 警告: {len(empty_datasets)}/{len(datasets)} 个数据集返回空结果")
                for empty_ds in empty_datasets:
                    self.logger.warning(f"  - {empty_ds['name']}: {empty_ds['reason']} - SQL: {empty_ds['sql'][:200]}...")
            
            for i, s in enumerate(sections, 1):
                lines.append(f"\n---\nDataset {i}: {s['name']}\nSQL:\n{s['sql']}\n\n{s['summary']}")
            user_prompt = "\n".join(lines)
            
            # Log what's being sent to LLM for debugging
            prompt_length = len(user_prompt)
            self.logger.info(f"DataReports prompt length: {prompt_length} characters")
            self.logger.info(f"DataReports prompt preview (first 1000 chars): {user_prompt[:1000]}")
            print(f"\n[DEBUG] Prompt length: {prompt_length} chars")
            print(f"[DEBUG] Prompt preview: {user_prompt[:500]}...")

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

    def list_conversations(self):
        """List all conversations"""
        return self.conversation_manager.list_conversations()
    
    def get_conversation_details(self, session_id):
        """Get conversation details"""
        return self.conversation_manager.get_conversation_details(session_id)
    
    def get_session_details(self, session_id):
        """Get session details (alias for get_conversation_details)"""
        return self.get_conversation_details(session_id)

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
            
            # Add followup conversations
            for followup in context.conversation_history:
                if followup.get('type') == 'followup':
                    export_data['conversation_history'].append(followup)
            
            # Export using export manager
            pdf_path = self.export_manager.export_to_pdf(export_data, filename)
            
            if pdf_path:
                print(f"✅ PDF export successful: {pdf_path}")
                return pdf_path
            else:
                print("❌ PDF export failed")
                return None
                
        except Exception as e:
            print(f"❌ PDF export failed: {str(e)}")
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
                            if isinstance(follow_data, str):
                                follow_data_list = json.loads(follow_data)
                            else:
                                follow_data_list = follow_data
                            
                            if follow_data_list:
                                df_follow = pd.DataFrame(follow_data_list)
                                if not df_follow.empty:
                                    df_follow['_conversation_type'] = 'followup'
                                    df_follow['_question'] = followup.get('question', '')
                                    df_follow['_timestamp'] = followup.get('timestamp', '')
                                    all_data.append(df_follow)
                                    has_data = True
                        except Exception as e:
                            print(f"Warning: Could not parse followup data: {e}")
            
            if not has_data:
                print("❌ No data found to export")
                return None
            
            # Combine all data
            combined_df = pd.concat(all_data, ignore_index=True, sort=False)
            
            # Export using export manager
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

    def ask_and_analyze(self, question, create_session=True, selected_option=None,
                       has_chart=False, chart_spec=None, chart_data=None, 
                       chart_type=None, chart_error=None, doc_file_url=None,
                       doc_filename=None, excel_file_url=None, excel_filename=None,
                       session_id=None, language=None):
        """Complete analysis process: generate SQL, execute, and analyze (compatible with web_api.py)"""
        try:
            print(f"Question: {question}")
            
            # 生成SQL
            print("Generating SQL query...")
            sql = self.generate_sql(question, mode=selected_option, session_id=session_id)
            if not sql or sql.startswith("生成SQL时出错"):
                print("Unable to generate SQL for this question")
                return None, None, None

            # 执行SQL
            print(f"Executing: {sql}")
            df = self.execute_sql(sql)
            if df is None:
                print("Execution failed")
                return None, "Query execution failed.", None

            # 分析结果
            print("\\nAnalyzing results...")
            analysis = self.analyze_results(question, df)
            print(f"\\nAnalysis Results:\\n{analysis}")

            # 保存会话（如果需要）
            if create_session:
                try:
                    columns = list(df.columns) if hasattr(df, 'columns') else []
                except Exception:
                    columns = []
                session_id = self.conversation_manager.create_conversation(
                    question=question,
                    sql_query=sql,
                    query_result=df,
                    columns=columns,
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
            return df, analysis, session_id
            
        except Exception as e:
            print(f"Error during analysis: {str(e)}")
            return None, f"Analysis error: {str(e)}", None
    
    def interactive_mode(self):
        """Interactive analysis mode"""
        print("\\n=== E3 MySQL数据库交互分析模式 ===")
        print("输入您的问题，我将帮您分析数据")
        print("输入 'quit' 或 'exit' 退出")
        print("输入 'schema' 查看数据库结构")
        print("输入 'tables' 查看所有表")
        
        while True:
            try:
                question = input("\\n❓ 请输入您的问题: ").strip()
                
                if question.lower() in ['quit', 'exit', '退出']:
                    break
                elif question.lower() == 'schema':
                    print(self.get_schema_info())
                    continue
                elif question.lower() == 'tables':
                    tables = self.get_table_list()
                    print(f"\\n数据库包含 {len(tables)} 个表:")
                    for i, table in enumerate(tables, 1):
                        print(f"  {i}. {table}")
                    continue
                elif not question:
                    continue
                
                # 分析数据
                result = self.analyze_data(None, question)
                
                # 显示结果
                print(f"\\n📊 分析结果:")
                print(f"SQL查询: {result['sql']}")
                
                if 'error' in result:
                    print(f"❌ 错误: {result['error']}")
                else:
                    if result['data'] is not None and not result['data'].empty:
                        print(f"📈 数据行数: {len(result['data'])}")
                        print(f"📋 数据预览:\\n{result['data'].head().to_string()}")
                    
                    print(f"\\n🎯 AI分析:\\n{result['analysis']}")
                
            except KeyboardInterrupt:
                print("\\n\\n👋 再见！")
                break
            except Exception as e:
                print(f"❌ 发生错误: {e}")
        
        # 清理
        self.disconnect_from_database()

def main():
    """主函数"""
    print("=== E3 MySQL数据库AI分析系统 ===")
    
    # 创建分析器
    analyzer = E3MySQLAnalyzer()
    
    # 检查数据库连接
    if not analyzer.connection or not analyzer.connection.is_connected():
        print("❌ 无法连接到数据库，请检查配置")
        print("\\n请确保:")
        print("1. MySQL服务正在运行")
        print("2. 数据库 'e3_database' 存在")
        print("3. 用户名和密码正确")
        print("4. 在.env文件中配置了正确的数据库连接信息")
        return
    
    # 显示数据库信息
    tables = analyzer.get_table_list()
    print(f"\\n📊 数据库包含 {len(tables)} 个表")
    
    # 启动交互模式
    analyzer.interactive_mode()

if __name__ == "__main__":
    main()