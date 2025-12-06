#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
E3数据库初始化脚本
将E3.new.sql文件导入到MySQL数据库
"""

import os
import sys
import mysql.connector
from mysql.connector import Error
import re
from dotenv import load_dotenv

# Load environment variables
load_dotenv('.env.mysql')

class DatabaseInitializer:
    """数据库初始化器"""
    
    def __init__(self):
        """Initialize database initializer"""
        self.mysql_config = {
            'host': os.getenv('MYSQL_HOST', 'localhost'),
            'port': int(os.getenv('MYSQL_PORT', 3306)),
            'user': os.getenv('MYSQL_USER', 'root'),
            'password': os.getenv('MYSQL_PASSWORD', ''),
            'charset': 'utf8mb4'
        }
        
        self.database_name = os.getenv('MYSQL_DATABASE', 'esp92032_REDACTED')
        self.sql_file_path = r"./E3.new.sql"
        
        self.connection = None
    
    def connect_to_mysql(self):
        """连接到MySQL服务器（不指定数据库）"""
        try:
            self.connection = mysql.connector.connect(**self.mysql_config)
            if self.connection.is_connected():
                print(f"✅ 成功连接到MySQL服务器")
                return True
        except Error as e:
            print(f"❌ 连接MySQL服务器时出错: {e}")
            return False
    
    def create_database(self, auto_recreate=False):
        """创建数据库"""
        if not self.connection or not self.connection.is_connected():
            return False
        
        try:
            cursor = self.connection.cursor()
            
            # 检查数据库是否存在
            cursor.execute(f"SHOW DATABASES LIKE '{self.database_name}'")
            result = cursor.fetchone()
            
            if result:
                print(f"⚠️  数据库 '{self.database_name}' 已存在")
                if auto_recreate:
                    print("自动删除并重新创建数据库...")
                    cursor.execute(f"DROP DATABASE `{self.database_name}`")
                    print(f"🗑️  已删除数据库 '{self.database_name}'")
                else:
                    response = input("是否要删除并重新创建？(y/N): ").strip().lower()
                    if response == 'y':
                        cursor.execute(f"DROP DATABASE `{self.database_name}`")
                        print(f"🗑️  已删除数据库 '{self.database_name}'")
                    else:
                        print("取消操作")
                        cursor.close()
                        return False
            
            # 创建数据库
            cursor.execute(f"CREATE DATABASE `{self.database_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")
            print(f"✅ 成功创建数据库 '{self.database_name}'")
            
            cursor.close()
            return True
            
        except Error as e:
            print(f"❌ 创建数据库时出错: {e}")
            return False
    
    def read_sql_file(self):
        """读取SQL文件内容"""
        if not os.path.exists(self.sql_file_path):
            print(f"❌ SQL文件不存在: {self.sql_file_path}")
            return None
        
        # 尝试不同的编码
        encodings = ['utf-8', 'gbk', 'latin-1', 'cp1252']
        
        for encoding in encodings:
            try:
                with open(self.sql_file_path, 'r', encoding=encoding) as file:
                    content = file.read()
                print(f"✅ 成功读取SQL文件 (编码: {encoding})")
                return content
            except UnicodeDecodeError:
                continue
        
        print(f"❌ 无法读取SQL文件，尝试了所有编码: {encodings}")
        return None
    
    def clean_sql_content(self, content):
        """清理SQL内容"""
        # 移除注释和无效语句
        lines = []
        for line in content.split('\n'):
            line = line.strip()
            if line and not line.startswith('--'):
                # 跳过包含$table占位符的DROP语句
                if 'DROP TABLE IF EXISTS $table' in line:
                    continue
                lines.append(line)
        
        return '\n'.join(lines)
    
    def split_sql_statements(self, content):
        """分割SQL语句"""
        # 改进的SQL语句分割
        statements = []
        current_statement = ""
        in_string = False
        escape_next = False
        
        i = 0
        while i < len(content):
            char = content[i]
            
            if escape_next:
                current_statement += char
                escape_next = False
                i += 1
                continue
            
            if char == '\\':
                escape_next = True
                current_statement += char
                i += 1
                continue
            
            if char in ("'", '"'):
                in_string = not in_string
                current_statement += char
                i += 1
                continue
            
            if not in_string and char == ';':
                current_statement += char
                statement = current_statement.strip()
                if statement and not statement.startswith('--'):
                    statements.append(statement)
                current_statement = ""
                i += 1
                continue
            
            current_statement += char
            i += 1
        
        # 添加最后一个语句（如果没有分号结尾）
        if current_statement.strip():
            statement = current_statement.strip()
            if statement and not statement.startswith('--'):
                statements.append(statement)
        
        return statements
    
    def execute_sql_statements(self, statements):
        """执行SQL语句"""
        if not self.connection or not self.connection.is_connected():
            return False
        
        try:
            # 连接到指定数据库
            self.connection.database = self.database_name
            cursor = self.connection.cursor()
            
            success_count = 0
            error_count = 0
            
            print(f"\\n📝 开始执行 {len(statements)} 条SQL语句...")
            
            for i, statement in enumerate(statements, 1):
                if not statement.strip():
                    continue
                
                try:
                    # 为每个语句创建新的cursor
                    cursor = self.connection.cursor()
                    
                    # 分割多个语句（如果存在）
                    sub_statements = [s.strip() for s in statement.split(';') if s.strip()]
                    
                    for sub_statement in sub_statements:
                        if sub_statement:
                            cursor.execute(sub_statement)
                    
                    cursor.close()
                    success_count += 1
                    
                    if i % 50 == 0:  # 每50条语句显示一次进度
                        print(f"   进度: {i}/{len(statements)} ({success_count} 成功, {error_count} 失败)")
                        
                except Error as e:
                    error_count += 1
                    print(f"   ❌ 语句 {i} 执行失败: {str(e)[:100]}...")
                    
                    # 如果是表已存在的错误，继续执行
                    if "already exists" in str(e).lower():
                        continue
                    
                    # 对于其他严重错误，自动继续
                    if error_count > 10:
                        print(f"\\n已有 {error_count} 个错误，自动继续执行...")
            
            # 提交事务
            self.connection.commit()
            
            print(f"\\n✅ SQL执行完成:")
            print(f"   成功: {success_count} 条")
            print(f"   失败: {error_count} 条")
            
            return True
            
        except Error as e:
            print(f"❌ 执行SQL时出错: {e}")
            return False
    
    def verify_import(self):
        """验证导入结果"""
        if not self.connection or not self.connection.is_connected():
            return False
        
        try:
            cursor = self.connection.cursor()
            
            # 获取表数量
            cursor.execute("SHOW TABLES")
            tables = cursor.fetchall()
            table_count = len(tables)
            
            print(f"\\n📊 导入验证:")
            print(f"   数据库: {self.database_name}")
            print(f"   表数量: {table_count}")
            
            if table_count > 0:
                print(f"   表列表 (前10个):")
                for i, table in enumerate(tables[:10]):
                    print(f"     {i+1}. {table[0]}")
                
                if table_count > 10:
                    print(f"     ... 还有 {table_count - 10} 个表")
            
            cursor.close()
            return True
            
        except Error as e:
            print(f"❌ 验证导入时出错: {e}")
            return False
    
    def disconnect(self):
        """断开数据库连接"""
        if self.connection and self.connection.is_connected():
            self.connection.close()
            print("\\n🔌 已断开数据库连接")
    
    def initialize_database(self, auto_recreate=False):
        """完整的数据库初始化流程"""
        print("=== E3数据库初始化 ===\n")
        
        # 1. 连接MySQL服务器
        if not self.connect_to_mysql():
            return False
        
        # 2. 创建数据库
        if not self.create_database(auto_recreate):
            self.disconnect()
            return False
        
        # 3. 读取SQL文件
        sql_content = self.read_sql_file()
        if not sql_content:
            self.disconnect()
            return False
        
        # 4. 清理和分割SQL语句
        print("🧹 清理SQL内容...")
        cleaned_content = self.clean_sql_content(sql_content)
        
        print("✂️  分割SQL语句...")
        statements = self.split_sql_statements(cleaned_content)
        print(f"   找到 {len(statements)} 条SQL语句")
        
        # 5. 执行SQL语句
        if not self.execute_sql_statements(statements):
            self.disconnect()
            return False
        
        # 6. 验证导入
        self.verify_import()
        
        # 7. 断开连接
        self.disconnect()
        
        print("\\n🎉 数据库初始化完成！")
        return True

def main():
    """主函数"""
    import sys
    
    initializer = DatabaseInitializer()
    
    print("这将初始化E3数据库到MySQL")
    print(f"SQL文件: {initializer.sql_file_path}")
    print(f"目标数据库: {initializer.database_name}")
    print()
    
    # 检查是否有自动执行参数
    auto_confirm = '--auto' in sys.argv or '-y' in sys.argv
    auto_recreate = '--force' in sys.argv or auto_confirm
    
    if not auto_confirm:
        response = input("确认开始初始化？(y/N): ").strip().lower()
        if response != 'y':
            print("取消操作")
            return
    else:
        print("自动确认初始化...")
    
    success = initializer.initialize_database(auto_recreate)
    
    if success:
        print("\n✅ 初始化成功！现在可以运行 main_mysql.py 来使用AI分析功能")
    else:
        print("\n❌ 初始化失败，请检查错误信息")

if __name__ == "__main__":
    main()