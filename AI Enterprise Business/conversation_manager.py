#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
对话管理机制
实现会话存储、上下文管理和追问功能
"""

import json
import os
import uuid
import datetime
import traceback
from typing import Dict, List, Optional, Any
from dataclasses import dataclass, asdict, field
import pandas as pd
import numpy as np
from datetime import date
import mysql.connector
from mysql.connector import Error

def safe_json_dumps(obj, **kwargs):
    """安全的JSON序列化，处理特殊数据类型"""
    def default_serializer(o):
        # 首先检查是否为NaT或NaN值
        if pd.isna(o):
            return None
        elif isinstance(o, (pd.Timestamp, datetime.datetime, date)):
            # 对于时间类型，先检查是否为NaT，然后再调用isoformat
            try:
                return o.isoformat() if hasattr(o, 'isoformat') else str(o)
            except (ValueError, AttributeError):
                # 如果isoformat失败（比如NaT），返回None
                return None
        elif isinstance(o, (np.integer, np.floating)):
            return o.item()
        elif isinstance(o, np.ndarray):
            return o.tolist()
        return str(o)
    
    return json.dumps(obj, default=default_serializer, **kwargs)


@dataclass
class ConversationContext:
    """对话上下文管理类"""
    session_id: str
    original_question: str
    generated_sql: str
    query_result: str  # 存储为JSON字符串
    analysis_result: str
    language: str
    created_at: str
    title: Optional[str] = None
    updated_at: Optional[str] = None
    conversation_history: List[Dict[str, str]] = field(default_factory=list)  # 存储追问历史
    selected_option: Optional[str] = None  # 添加选择的模式
    # 图表相关字段
    has_chart: Optional[bool] = False
    chart_spec: Optional[Dict[str, Any]] = None
    chart_data: Optional[List[Dict[str, Any]]] = None
    chart_type: Optional[str] = None
    chart_error: Optional[str] = None
    # 导出相关字段
    doc_file_url: Optional[str] = None
    doc_filename: Optional[str] = None
    excel_file_url: Optional[str] = None
    excel_filename: Optional[str] = None
    ppt_file_url: Optional[str] = None
    ppt_filename: Optional[str] = None
    # 图形相关字段
    graphics_file_url: Optional[str] = None
    graphics_filename: Optional[str] = None
    graphics_error: Optional[str] = None

    def to_dict(self) -> Dict[str, Any]:
        """转换为字典格式"""
        return asdict(self)

    @classmethod
    def from_dict(cls, data: Dict[str, Any]) -> 'ConversationContext':
        """从字典创建实例"""
        return cls(**data)

    def add_followup(self, question: str, response: str):
        """添加追问记录"""
        self.conversation_history.append({
            "timestamp": datetime.datetime.now().isoformat(),
            "question": question,
            "analysis": response,
            "type": "followup",
            "selected_option": self.selected_option
        })


class SessionManager:
    """Conversation persistence backed by MySQL."""

    def __init__(self, mysql_config: Optional[Dict[str, Any]] = None, table_name: Optional[str] = None):
        self.mysql_config = mysql_config or {
            'host': os.getenv('MYSQL_HOST', 'localhost'),
            'port': int(os.getenv('MYSQL_PORT', 3306)),
            'database': os.getenv('MYSQL_DATABASE', 'esp92032_e3'),
            'user': os.getenv('MYSQL_USER', 'root'),
            'password': os.getenv('MYSQL_PASSWORD', ''),
            'charset': 'utf8mb4'
        }
        self.table_name = table_name or os.getenv('CONVERSATIONS_TABLE', 'ai_conversations')
        self._ensure_table()

    # ---------- Internal helpers ----------
    def _get_connection(self):
        """Create a new MySQL connection using stored config."""
        return mysql.connector.connect(**self.mysql_config)

    def _ensure_table(self):
        """Create the conversations table if it does not already exist."""
        create_sql = f"""
        CREATE TABLE IF NOT EXISTS `{self.table_name}` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `session_id` VARCHAR(64) NOT NULL,
            `user_id` VARCHAR(191) NULL,
            `title` VARCHAR(255) NULL,
            `original_question` TEXT,
            `generated_sql` MEDIUMTEXT,
            `query_result` LONGTEXT,
            `analysis_result` LONGTEXT,
            `language` VARCHAR(32),
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `conversation_history` LONGTEXT,
            `selected_option` VARCHAR(128),
            `has_chart` TINYINT(1) NOT NULL DEFAULT 0,
            `chart_spec` LONGTEXT,
            `chart_data` LONGTEXT,
            `chart_type` VARCHAR(128),
            `chart_error` TEXT,
            `doc_file_url` VARCHAR(255),
            `doc_filename` VARCHAR(255),
            `excel_file_url` VARCHAR(255),
            `excel_filename` VARCHAR(255),
            `ppt_file_url` VARCHAR(255),
            `ppt_filename` VARCHAR(255),
            `graphics_file_url` VARCHAR(255),
            `graphics_filename` VARCHAR(255),
            `graphics_error` TEXT,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_session_id` (`session_id`),
            KEY `idx_created_at` (`created_at`)
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        """
        conn = None
        try:
            conn = self._get_connection()
            cursor = conn.cursor()
            cursor.execute(create_sql)
            conn.commit()
            # Ensure newer columns exist when upgrading from older schema versions
            self._ensure_column(cursor, 'title', "VARCHAR(255) NULL")
            conn.commit()
            cursor.close()
        except Error as e:
            print(f"⚠️ 无法创建会话表: {e}")
        finally:
            if conn:
                conn.close()

    def _ensure_column(self, cursor, column_name: str, column_def: str):
        """Ensure a specific column exists on the conversations table."""
        try:
            cursor.execute(
                "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS "
                "WHERE TABLE_SCHEMA=%s AND TABLE_NAME=%s AND COLUMN_NAME=%s",
                (self.mysql_config['database'], self.table_name, column_name)
            )
            if cursor.fetchone():
                return
            cursor.execute(
                f"ALTER TABLE `{self.table_name}` ADD COLUMN `{column_name}` {column_def}"
            )
        except Error as e:
            print(f"⚠️ 确保列 {column_name} 存在时出错: {e}")

    def _session_id_exists(self, session_id: str) -> bool:
        conn = None
        try:
            conn = self._get_connection()
            cursor = conn.cursor()
            cursor.execute(f"SELECT 1 FROM `{self.table_name}` WHERE `session_id`=%s LIMIT 1", (session_id,))
            exists = cursor.fetchone() is not None
            cursor.close()
            return exists
        except Error as e:
            print(f"⚠️ 检查会话ID时出错: {e}")
            return False
        finally:
            if conn:
                conn.close()

    def generate_session_id(self) -> str:
        """Generate a unique session ID."""
        for _ in range(5):
            session_id = str(uuid.uuid4())[:8]
            if not self._session_id_exists(session_id):
                return session_id
        # Fallback to UUID without truncation if collisions persist
        return str(uuid.uuid4())

    def _row_to_context(self, row: Dict[str, Any]) -> ConversationContext:
        """Convert a DB row to ConversationContext."""
        conversation_history = []
        if row.get('conversation_history'):
            try:
                conversation_history = json.loads(row['conversation_history'])
            except Exception:
                conversation_history = []

        def _parse_json(value):
            if not value:
                return None
            try:
                return json.loads(value)
            except Exception:
                return None

        created_at = row.get('created_at')
        created_at_str = created_at.isoformat() if isinstance(created_at, datetime.datetime) else str(created_at)
        updated_at = row.get('updated_at')
        updated_at_str = updated_at.isoformat() if isinstance(updated_at, datetime.datetime) else (
            str(updated_at) if updated_at else None
        )

        title = row.get('title') or row.get('original_question', '')

        return ConversationContext(
            session_id=row['session_id'],
            original_question=row.get('original_question', ''),
            title=title,
            generated_sql=row.get('generated_sql', ''),
            query_result=row.get('query_result', ''),
            analysis_result=row.get('analysis_result', ''),
            language=row.get('language', 'english'),
            created_at=created_at_str,
            updated_at=updated_at_str,
            conversation_history=conversation_history,
            selected_option=row.get('selected_option'),
            has_chart=bool(row.get('has_chart', 0)),
            chart_spec=_parse_json(row.get('chart_spec')),
            chart_data=_parse_json(row.get('chart_data')),
            chart_type=row.get('chart_type'),
            chart_error=row.get('chart_error'),
            doc_file_url=row.get('doc_file_url'),
            doc_filename=row.get('doc_filename'),
            excel_file_url=row.get('excel_file_url'),
            excel_filename=row.get('excel_filename'),
            ppt_file_url=row.get('ppt_file_url'),
            ppt_filename=row.get('ppt_filename'),
            graphics_file_url=row.get('graphics_file_url'),
            graphics_filename=row.get('graphics_filename'),
            graphics_error=row.get('graphics_error')
        )

    # ---------- Public API ----------
    def create_session(self, question: str, sql: str, query_result: Any,
                       analysis: str, language: str, selected_option: str = None,
                       has_chart: bool = False, chart_spec: Dict[str, Any] = None,
                       chart_data: List[Dict[str, Any]] = None, chart_type: str = None,
                       chart_error: str = None, doc_file_url: str = None,
                       doc_filename: str = None, excel_file_url: str = None,
                       excel_filename: str = None, ppt_file_url: str = None,
                       ppt_filename: str = None, graphics_file_url: str = None,
                       graphics_filename: str = None, graphics_error: str = None,
                       user_id: Optional[str] = None) -> str:
        """Create a new conversation session."""
        session_id = self.generate_session_id()
        now = datetime.datetime.now()

        session_user_id = user_id or os.getenv('DEFAULT_USER_ID', '6')

        if hasattr(query_result, 'to_dict'):
            result_data = query_result.to_dict(orient='records')
            result_json = safe_json_dumps(result_data, ensure_ascii=False)
        else:
            result_json = safe_json_dumps(str(query_result), ensure_ascii=False)

        conversation_history = [{
            "timestamp": now.isoformat(),
            "question": question,
            "response": analysis,
            "type": "initial",
            "selected_option": selected_option
        }]

        def _dump(value):
            if value is None:
                return None
            return safe_json_dumps(value, ensure_ascii=False)

        conn = None
        cursor = None
        try:
            conn = self._get_connection()
            cursor = conn.cursor()
            insert_sql = f"""
            INSERT INTO `{self.table_name}` (
                session_id, user_id, title, original_question, generated_sql, query_result,
                analysis_result, language, created_at, updated_at,
                conversation_history, selected_option, has_chart,
                chart_spec, chart_data, chart_type, chart_error,
                doc_file_url, doc_filename, excel_file_url, excel_filename,
                ppt_file_url, ppt_filename, graphics_file_url, graphics_filename, graphics_error
            ) VALUES (
                %s, %s, %s, %s, %s, %s,
                %s, %s, %s, %s,
                %s, %s, %s,
                %s, %s, %s, %s,
                %s, %s, %s, %s,
                %s, %s, %s, %s, %s
            )
            """
            cursor.execute(insert_sql, (
                session_id, session_user_id, question, question, sql, result_json,
                analysis, language, now, now,
                safe_json_dumps(conversation_history, ensure_ascii=False),
                selected_option, 1 if has_chart else 0,
                _dump(chart_spec), _dump(chart_data), chart_type, chart_error,
                doc_file_url, doc_filename, excel_file_url, excel_filename,
                ppt_file_url, ppt_filename, graphics_file_url, graphics_filename, graphics_error
            ))
            conn.commit()
            print(f"✅ Successfully saved conversation session_id={session_id}, user_id={session_user_id}")
            return session_id
        except Error as e:
            print(f"❌ 保存会话到数据库失败: {e}")
            print(f"   Session ID: {session_id}, User ID: {session_user_id}")
            traceback.print_exc()
            # Rollback transaction on error
            if conn:
                try:
                    conn.rollback()
                except Exception as rollback_error:
                    print(f"⚠️ Rollback failed: {rollback_error}")
            # Re-raise exception so caller knows it failed
            raise
        except Exception as e:
            print(f"❌ Unexpected error saving conversation: {e}")
            print(f"   Session ID: {session_id}, User ID: {session_user_id}")
            traceback.print_exc()
            if conn:
                try:
                    conn.rollback()
                except Exception:
                    pass
            raise
        finally:
            if cursor:
                cursor.close()
            if conn:
                conn.close()

    def get_session(self, session_id: str) -> Optional[ConversationContext]:
        conn = None
        try:
            conn = self._get_connection()
            cursor = conn.cursor(dictionary=True)
            cursor.execute(f"SELECT * FROM `{self.table_name}` WHERE `session_id`=%s", (session_id,))
            row = cursor.fetchone()
            cursor.close()
            if not row:
                return None
            return self._row_to_context(row)
        except Error as e:
            print(f"⚠️ 获取会话失败: {e}")
            return None
        finally:
            if conn:
                conn.close()

    def get_all_sessions(self) -> Dict[str, ConversationContext]:
        """Legacy API retained for compatibility."""
        sessions = {}
        conn = None
        try:
            conn = self._get_connection()
            cursor = conn.cursor(dictionary=True)
            cursor.execute(f"SELECT * FROM `{self.table_name}`")
            for row in cursor.fetchall():
                context = self._row_to_context(row)
                sessions[context.session_id] = context
            cursor.close()
        except Error as e:
            print(f"⚠️ 获取所有会话失败: {e}")
        finally:
            if conn:
                conn.close()
        return sessions

    def delete_session(self, session_id: str) -> bool:
        conn = None
        try:
            conn = self._get_connection()
            cursor = conn.cursor()
            cursor.execute(f"DELETE FROM `{self.table_name}` WHERE `session_id`=%s", (session_id,))
            affected = cursor.rowcount
            conn.commit()
            cursor.close()
            return affected > 0
        except Error as e:
            print(f"⚠️ 删除会话失败: {e}")
            return False
        finally:
            if conn:
                conn.close()

    def update_session(self, session_id: str, context: ConversationContext):
        conn = None
        cursor = None
        try:
            conn = self._get_connection()
            cursor = conn.cursor()
            update_sql = f"""
            UPDATE `{self.table_name}`
            SET title=%s,
                original_question=%s,
                generated_sql=%s,
                query_result=%s,
                analysis_result=%s,
                language=%s,
                conversation_history=%s,
                selected_option=%s,
                has_chart=%s,
                chart_spec=%s,
                chart_data=%s,
                chart_type=%s,
                chart_error=%s,
                doc_file_url=%s,
                doc_filename=%s,
                excel_file_url=%s,
                excel_filename=%s,
                ppt_file_url=%s,
                ppt_filename=%s,
                updated_at=%s
            WHERE session_id=%s
            """
            def _dump(value):
                if value is None:
                    return None
                return safe_json_dumps(value, ensure_ascii=False)

            cursor.execute(update_sql, (
                context.title or context.original_question,
                context.original_question,
                context.generated_sql,
                context.query_result,
                context.analysis_result,
                context.language,
                safe_json_dumps(context.conversation_history, ensure_ascii=False),
                context.selected_option,
                1 if context.has_chart else 0,
                _dump(context.chart_spec),
                _dump(context.chart_data),
                context.chart_type,
                context.chart_error,
                context.doc_file_url,
                context.doc_filename,
                context.excel_file_url,
                context.excel_filename,
                getattr(context, 'ppt_file_url', None),
                getattr(context, 'ppt_filename', None),
                datetime.datetime.now(),
                session_id
            ))
            affected_rows = cursor.rowcount
            conn.commit()
            print(f"✅ Successfully updated conversation session_id={session_id}, affected_rows={affected_rows}")
            return affected_rows > 0
        except Error as e:
            print(f"❌ 更新会话失败: {e}")
            print(f"   Session ID: {session_id}")
            traceback.print_exc()
            if conn:
                try:
                    conn.rollback()
                except Exception as rollback_error:
                    print(f"⚠️ Rollback failed: {rollback_error}")
            raise
        except Exception as e:
            print(f"❌ Unexpected error updating conversation: {e}")
            print(f"   Session ID: {session_id}")
            traceback.print_exc()
            if conn:
                try:
                    conn.rollback()
                except Exception:
                    pass
            raise
        finally:
            if cursor:
                cursor.close()
            if conn:
                conn.close()

    def get_session_summary(self) -> List[Dict[str, str]]:
        summaries: List[Dict[str, str]] = []
        conn = None
        try:
            conn = self._get_connection()
            cursor = conn.cursor(dictionary=True)
            cursor.execute(
                f"SELECT session_id, title, original_question, created_at, updated_at, language, conversation_history "
                f"FROM `{self.table_name}` ORDER BY created_at DESC"
            )
            rows = cursor.fetchall()
            cursor.close()
            for row in rows:
                question = row.get('original_question') or ''
                title = row.get('title') or question
                summary = title[:50] + ("..." if len(title) > 50 else "")
                followup_count = 0
                if row.get('conversation_history'):
                    try:
                        history = json.loads(row['conversation_history'])
                        followup_count = len([h for h in history if h.get('type') == 'followup'])
                    except Exception:
                        followup_count = 0
                created_at = row.get('created_at')
                created_at_str = created_at.isoformat() if isinstance(created_at, datetime.datetime) else str(created_at)
                updated_at = row.get('updated_at')
                if isinstance(updated_at, datetime.datetime):
                    updated_at_str = updated_at.isoformat()
                else:
                    updated_at_str = str(updated_at) if updated_at else None
                created_at_human = ""
                if created_at_str:
                    try:
                        created_at_human = datetime.datetime.fromisoformat(created_at_str).strftime('%Y-%m-%d %H:%M:%S')
                    except ValueError:
                        created_at_human = created_at_str
                summaries.append({
                    "session_id": row['session_id'],
                    "question_summary": summary,
                    "title": title,
                    "created_at": created_at_str,
                    "created_at_formatted": created_at_human,
                    "updated_at": updated_at_str,
                    "language": row.get('language', 'english'),
                    "followup_count": followup_count
                })
        except Error as e:
            print(f"⚠️ 获取会话摘要失败: {e}")
        finally:
            if conn:
                conn.close()
        return summaries

    def reconstruct_query_result(self, session_id: str) -> Optional[pd.DataFrame]:
        context = self.get_session(session_id)
        if not context:
            return None
        try:
            data = json.loads(context.query_result)
            if isinstance(data, list):
                return pd.DataFrame(data)
            return pd.DataFrame([{"result": data}])
        except Exception as e:
            print(f"⚠️ 重建查询结果失败: {e}")
            return None

    def update_session_title(self, session_id: str, title: str) -> bool:
        """Update only the title of a session."""
        conn = None
        try:
            conn = self._get_connection()
            cursor = conn.cursor()
            cursor.execute(
                f"UPDATE `{self.table_name}` SET title=%s, updated_at=%s WHERE session_id=%s",
                (title, datetime.datetime.now(), session_id)
            )
            affected = cursor.rowcount
            conn.commit()
            cursor.close()
            return affected > 0
        except Error as e:
            print(f"⚠️ 更新会话标题失败: {e}")
            return False
        finally:
            if conn:
                conn.close()


class ConversationManager:
    """对话管理器 - 整合会话管理和追问功能"""
    
    def __init__(self, openai_client, model: str = "gpt-4", mysql_config: Optional[Dict[str, Any]] = None):
        self.session_manager = SessionManager(mysql_config=mysql_config)
        self.openai_client = openai_client
        self.model = model
    
    def create_new_conversation(self, question: str, sql: str, query_result: Any, 
                               analysis: str, language: str, user_id: Optional[str] = None) -> str:
        """创建新的对话会话"""
        return self.session_manager.create_session(
            question, sql, query_result, analysis, language, user_id=user_id
        )
    
    def create_conversation(self, question: str, sql_query: str, query_result: Any, 
                           columns: List[str], analysis: str, selected_option: str = None,
                           has_chart: bool = False, chart_spec: Dict[str, Any] = None,
                           chart_data: List[Dict[str, Any]] = None, chart_type: str = None,
                           chart_error: str = None, doc_file_url: str = None,
                           doc_filename: str = None, excel_file_url: str = None,
                           excel_filename: str = None, graphics_file_url: str = None,
                           graphics_filename: str = None, graphics_error: str = None,
                           user_id: Optional[str] = None) -> str:
        """创建新的对话会话 - 兼容main.py的调用接口"""
        # 检测语言 - 使用与main.py相同的逻辑
        chinese_chars = sum(1 for char in question if '\u4e00' <= char <= '\u9fff')
        # 统计所有字符（包括中文字符和英文字母）
        total_chars = len([char for char in question if char.isalpha() or '\u4e00' <= char <= '\u9fff'])
        
        if total_chars == 0:
            language = 'english'
        else:
            chinese_ratio = chinese_chars / total_chars
            if chinese_ratio < 0.3:
                language = 'english'
            else:
                # 检测繁体中文
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
                traditional_count = sum(1 for char in question if char in traditional_chars)
                # 提高检测阈值，需要更多繁体字符才认为是繁体中文
                if traditional_count >= 1 and traditional_count / chinese_chars > 0.1:
                    language = 'traditional_chinese'
                else:
                    language = 'chinese'
        return self.session_manager.create_session(
            question, sql_query, query_result, analysis, language, selected_option,
            has_chart, chart_spec, chart_data, chart_type, chart_error,
            doc_file_url, doc_filename, excel_file_url, excel_filename,
            user_id=user_id
        )
    
    def ask_followup_question(self, session_id: str, followup_question: str) -> str:
        """在指定会话中进行追问"""
        context = self.session_manager.get_session(session_id)
        if not context:
            return "❌ 会话不存在"
        
        try:
            # 重建原始数据上下文
            query_result_df = self.session_manager.reconstruct_query_result(session_id)
            
            # 构建完整的上下文信息
            if query_result_df is not None:
                data_summary = self._format_data_for_context(query_result_df)
            else:
                data_summary = "无法重建原始数据"
            
            # 根据语言构建提示词
            if context.language == 'english':
                system_prompt = """You are a senior business data expert specializing in sales analytics, HR data analysis, and business forecasting. Please provide professional insights using natural, conversational language without rigid formatting."""

                user_prompt = f"""
Context: Craveva AI Enterprise Business data analysis

Original Question: {context.original_question}
Original Analysis: {context.analysis_result}
Data Overview: {data_summary}

Follow-up Question: {followup_question}

Please answer the follow-up question naturally based on the existing data and context.
"""
            else:
                system_prompt = """你是一位资深的商业数据专家，擅长销售数据分析、人力资源数据分析和商业预测。请用自然、对话式的语言提供专业洞察，不要使用僵硬的格式。"""

                user_prompt = f"""
分析背景：咖啡店销售数据分析

原始问题：{context.original_question}
原始分析：{context.analysis_result}
数据概览：{data_summary}

追问：{followup_question}

请基于现有数据和分析背景，自然地回答这个追问。
"""
            
            # 直接调用OpenAI
            print("🔄 正在处理追问...")
            response = self.openai_client.chat.completions.create(
                model=self.model,
                messages=[
                    {"role": "system", "content": system_prompt},
                    {"role": "user", "content": user_prompt}
                ],
                temperature=0.2,
                timeout=30
            )
            
            followup_response = response.choices[0].message.content.strip()
            
            # 添加到对话历史
            context.add_followup(followup_question, followup_response)
            self.session_manager.update_session(session_id, context)
            
            return followup_response
            
        except Exception as e:
            error_msg = f"❌ 追问处理失败: {e}"
            print(error_msg)
            return error_msg
    
    def _format_data_for_context(self, data: pd.DataFrame) -> str:
        """格式化数据用于上下文"""
        if len(data) > 10:
            # 创建前10行数据的文字描述
            data_text = "前10行数据：\n"
            for i, (idx, row) in enumerate(data.head(10).iterrows()):
                row_data = []
                for col in data.columns:
                    row_data.append(f"{col}: {row[col]}")
                data_text += f"第{i+1}行 - {', '.join(row_data)}\n"
            
            # 创建统计信息的文字描述
            stats_text = "数据统计：\n"
            if hasattr(data, 'describe'):
                desc = data.describe()
                for col in desc.columns:
                    stats_text += f"{col}列统计：\n"
                    for stat in desc.index:
                        stats_text += f"  {stat}: {desc.loc[stat, col]}\n"
            else:
                stats_text += "无统计信息"
            
            return f"""
数据概览: 共{len(data)}行数据
{data_text}
{stats_text}
"""
        else:
            # 创建所有数据的文字描述
            data_text = f"数据内容（共{len(data)}行）：\n"
            for i, (idx, row) in enumerate(data.iterrows()):
                row_data = []
                for col in data.columns:
                    row_data.append(f"{col}: {row[col]}")
                data_text += f"第{i+1}行 - {', '.join(row_data)}\n"
            return data_text
    
    def list_conversations(self) -> List[Dict[str, str]]:
        """列出所有对话会话"""
        return self.session_manager.get_session_summary()
    
    def get_conversation_details(self, session_id: str) -> Optional[ConversationContext]:
        """获取对话详情"""
        return self.session_manager.get_session(session_id)
    
    def delete_conversation(self, session_id: str) -> bool:
        """删除对话会话"""
        return self.session_manager.delete_session(session_id)