import json
import re
from concurrent.futures import ThreadPoolExecutor, as_completed
from functools import lru_cache


def _ensure_company_and_date(analyzer, sql: str, original_question: str) -> str:
    s = (sql or '').strip().rstrip(';')
    try:
        s = analyzer._ensure_company_filter(s)
    except Exception:
        pass
    try:
        s = analyzer._ensure_date_filter(s, original_question)
    except Exception:
        pass
    return s + (';' if not s.endswith(';') else '')


def decompose_question(analyzer, question: str, mode=None, session_id=None):
    """Use the analyzer's LLM to break the question into sub-questions."""
    print(f"🔍 [decompose_question] Starting decomposition for question: {question[:200]}...")
    try:
        model_to_use = analyzer.fast_model if (mode == 'Flash' and getattr(analyzer, 'fast_model', None)) else analyzer.model
        print(f"🔍 [decompose_question] Using model: {model_to_use}")
        
        mem = analyzer._build_memory_summary(session_id) if session_id else ''
        schema_info = analyzer.get_schema_summary(max_chars=4500)
        tables = analyzer.get_table_list()
        table_count = len(tables)
        schema_preview = schema_info
        
        dep_prompt = f"""
You are a senior data analyst. Break down the user's question into multiple focused sub-questions that can each be answered with a SQL query. Each sub-question should target a distinct metric or view.

**IMPORTANT: The database contains {table_count} tables. The system can access ALL {table_count} tables.**

Database schema (for awareness):
{schema_preview}

{('Conversation memory (if relevant):' + mem) if mem else ''}

User question:
{question}

Return STRICT JSON only with this shape:
{{
  "queries": [
    {{"name": "...", "purpose": "...", "question": "..."}},
    {{"name": "...", "purpose": "...", "question": "..."}}
  ]
}}

Rules:
- Each item MUST have a concise "question" suitable for SQL generation.
- Prefer separate questions for counts, breakdowns, trends, and top-N lists.
- Do NOT include SQL here; only sub-questions.
- Keep each question focused and specific.
"""
        print(f"🔍 [decompose_question] Calling LLM for decomposition...")
        resp = analyzer.client.chat.completions.create(
            model=model_to_use,
            messages=[{"role": "user", "content": dep_prompt}],
            temperature=0.0,
            max_tokens=2000  # Limit response size
        )
        dep_content = resp.choices[0].message.content.strip()
        print(f"🔍 [decompose_question] LLM response: {dep_content[:200]}...")
        
        # Clean up JSON if wrapped in markdown
        if dep_content.startswith('```json'):
            dep_content = dep_content[7:]
        if dep_content.startswith('```'):
            dep_content = dep_content[3:]
        if dep_content.endswith('```'):
            dep_content = dep_content[:-3]
        dep_content = dep_content.strip()
        
        payload = json.loads(dep_content)
        queries = payload.get("queries") if isinstance(payload, dict) else None
        if isinstance(queries, list) and len(queries) > 0:
            print(f"✅ [decompose_question] Parsed {len(queries)} sub-questions from LLM")
            normalized = []
            for i, item in enumerate(queries, 1):
                sq = (item.get("question") or "").strip()
                if not sq:
                    print(f"⚠️ [decompose_question] Skipping empty sub-question {i}")
                    continue
                normalized.append({
                    "name": item.get("name") or f"Query {i}",
                    "purpose": item.get("purpose") or "",
                    "question": sq
                })
            print(f"✅ [decompose_question] Returning {len(normalized)} normalized sub-questions")
            return normalized
        else:
            print(f"⚠️ [decompose_question] No valid queries found in LLM response")
    except json.JSONDecodeError as e:
        print(f"❌ [decompose_question] JSON decode error: {e}")
        print(f"❌ [decompose_question] Response was: {dep_content[:500] if 'dep_content' in locals() else 'N/A'}")
        import traceback
        traceback.print_exc()
    except Exception as e:
        print(f"❌ [decompose_question] Error during decomposition: {e}")
        import traceback
        traceback.print_exc()
    
    print(f"🔄 [decompose_question] Falling back to naive decomposition")

    # Fallback naive decomposition by splitting conjunctions
    try:
        parts = re.split(r"[，。；;\n]|\band\b|以及|分别|分别为|分别是|还有", question, flags=re.IGNORECASE)
        subqs = [p.strip() for p in parts if p and len(p.strip()) > 0]
        if subqs:
            return [{"name": f"Query {i+1}", "purpose": "Decomposed", "question": subqs[i]} for i in range(len(subqs))]
    except Exception:
        pass
    return []


def generate_sql_multi_decompose(analyzer, question: str, mode=None, session_id=None):
    """Optimized: Skip slow decomposition, use direct generate_sql_multi() for speed."""
    import time
    start_time = time.time()
    print(f"⚡ [generate_sql_multi_decompose] Starting (mode={mode}) - OPTIMIZED PATH")
    print(f"⚡ [generate_sql_multi_decompose] Question: {question[:200]}...")
    
    # OPTIMIZATION: Skip slow decomposition step, use direct generate_sql_multi()
    # This is 3-5x faster because:
    # - 1 LLM call instead of 1 + N calls
    # - No Vanna RAG lookups per sub-question
    # - Single prompt with full context
    
    # Fallback 1: Direct multi-SQL generation (FASTEST)
    print(f"⚡ [generate_sql_multi_decompose] Using direct generate_sql_multi() for speed")
    try:
        legacy = analyzer.generate_sql_multi(question, mode=mode, session_id=session_id)
        elapsed = time.time() - start_time
        if legacy:
            print(f"✅ [generate_sql_multi_decompose] Generated {len(legacy)} SQL queries in {elapsed:.2f}s")
            return legacy
        else:
            print(f"⚠️ [generate_sql_multi_decompose] Direct method returned empty list ({elapsed:.2f}s)")
    except Exception as e:
        elapsed = time.time() - start_time
        print(f"❌ [generate_sql_multi_decompose] Direct method failed ({elapsed:.2f}s): {e}")
        import traceback
        traceback.print_exc()
    
    # Fallback 2: Single SQL query (faster than decomposition)
    print(f"🔄 [generate_sql_multi_decompose] Using fallback: Single SQL query")
    try:
        single_sql = analyzer.generate_sql(question, mode=mode, session_id=session_id)
        elapsed = time.time() - start_time
        if single_sql:
            print(f"✅ [generate_sql_multi_decompose] Generated single SQL query in {elapsed:.2f}s")
            s = _ensure_company_and_date(analyzer, single_sql, question)
            return [{
                "name": "Query 1",
                "purpose": "Single query fallback",
                "sql": s
            }]
        else:
            print(f"❌ [generate_sql_multi_decompose] Single SQL also failed ({elapsed:.2f}s)")
    except Exception as e:
        elapsed = time.time() - start_time
        print(f"❌ [generate_sql_multi_decompose] Single SQL failed ({elapsed:.2f}s): {e}")
        import traceback
        traceback.print_exc()
    
    elapsed = time.time() - start_time
    print(f"❌ [generate_sql_multi_decompose] All methods failed ({elapsed:.2f}s)")
    return []


def ask_and_analyze_v2(analyzer, question: str, create_session=True, selected_option=None,
                       has_chart=False, chart_spec=None, chart_data=None,
                       chart_type=None, chart_error=None, doc_file_url=None,
                       doc_filename=None, excel_file_url=None, excel_filename=None,
                       session_id=None, language=None):
    """LLM-first multi-SQL flow: decompose -> Vanna SQL -> execute all -> multi-dataset analysis."""
    try:
        print(f"Question: {question}")

        sql_items = generate_sql_multi_decompose(analyzer, question, mode=selected_option, session_id=session_id)
        if not sql_items:
            print("Unable to generate SQL for this question")
            analyzer.logger.error(f"No SQL generated for question: {question}")
            return None, None, None
        
        # Log generated SQL queries
        print("\n" + "="*60)
        print("GENERATED SQL QUERIES")
        print("="*60)
        for idx, item in enumerate(sql_items, 1):
            sql = item.get('sql', '')
            name = item.get('name', f'Query {idx}')
            purpose = item.get('purpose', '')
            print(f"\n{idx}. {name}")
            if purpose:
                print(f"   Purpose: {purpose}")
            print(f"   SQL: {sql[:300]}...")
            analyzer.logger.info(f"Generated SQL {idx} ({name}): {sql[:500]}")
        print("="*60 + "\n")

        # Execute SQL queries in parallel for better performance
        datasets = []
        def execute_single_query(item):
            """Execute a single SQL query and return result (thread-safe)"""
            sql_exec = (item.get('sql') or '').strip().rstrip(';')
            if not sql_exec:
                print(f"⚠️ Skipping empty SQL for {item.get('name', 'Query')}")
                return None
            print(f"Executing: {sql_exec[:100]}...")
            try:
                # Use thread connection for parallel execution
                df = analyzer.execute_sql(sql_exec, use_thread_connection=True)
                return {
                    'name': item.get('name') or 'Query',
                    'purpose': item.get('purpose') or '',
                    'sql': sql_exec,
                    'df': df
                }
            except Exception as e:
                print(f"❌ Error executing query '{item.get('name', 'Query')}': {e}")
                return None
        
        # Execute all queries in parallel (max 5 concurrent)
        with ThreadPoolExecutor(max_workers=5) as executor:
            future_to_item = {executor.submit(execute_single_query, item): item for item in sql_items}
            for future in as_completed(future_to_item):
                result = future.result()
                if result is not None:
                    # Check if DataFrame is empty
                    try:
                        df = result['df']
                        is_empty = df.empty if hasattr(df, 'empty') else (len(df) == 0 if hasattr(df, '__len__') else True)
                        if is_empty:
                            print(f"⚠️ Query '{result['name']}' returned empty result")
                        else:
                            print(f"✅ Query '{result['name']}' returned {len(df)} rows")
                    except Exception as e:
                        print(f"⚠️ Error checking DataFrame for '{result.get('name', 'Query')}': {e}")
                    datasets.append(result)

        # Check if we have any datasets at all
        if not datasets:
            print("❌ No valid datasets generated from SQL queries")
            return None, "No data found for the generated queries. All SQL queries either failed or returned no results.", None

        # Check if all datasets are empty
        all_empty = True
        primary_df = None
        for d in datasets:
            if d['df'] is not None:
                try:
                    is_empty = d['df'].empty if hasattr(d['df'], 'empty') else (len(d['df']) == 0 if hasattr(d['df'], '__len__') else True)
                    if not is_empty:
                        all_empty = False
                        if primary_df is None:
                            primary_df = d['df']
                except Exception:
                    pass
        
        if all_empty:
            print("⚠️ All queries returned empty results")
            # Still proceed with analysis so AI can report "no data found"
        
        if primary_df is None and datasets:
            # Use first dataset even if empty (for error reporting)
            primary_df = datasets[0]['df']

        # Log dataset summary before analysis
        print("\n" + "="*60)
        print("DATASET SUMMARY BEFORE ANALYSIS")
        print("="*60)
        for idx, d in enumerate(datasets, 1):
            df = d.get('df')
            name = d.get('name', f'Dataset {idx}')
            sql = d.get('sql', '')
            if df is None:
                print(f"  {idx}. {name}: NULL (no data)")
                analyzer.logger.warning(f"Dataset {idx} ({name}): df is None")
            elif hasattr(df, 'empty') and df.empty:
                print(f"  {idx}. {name}: EMPTY (0 rows)")
                print(f"     SQL: {sql[:200]}...")
                analyzer.logger.warning(f"Dataset {idx} ({name}): Empty DataFrame - SQL: {sql[:500]}")
            else:
                row_count = len(df) if hasattr(df, '__len__') else 0
                col_count = len(df.columns) if hasattr(df, 'columns') else 0
                print(f"  {idx}. {name}: {row_count} rows, {col_count} columns")
                # Show first few values for numeric columns
                if row_count > 0:
                    numeric_cols = [c for c in df.columns if df[c].dtype in ['int64', 'float64']] if hasattr(df, 'columns') else []
                    if numeric_cols:
                        sample_summary = {}
                        for col in numeric_cols[:3]:  # First 3 numeric columns
                            try:
                                sample_summary[col] = {
                                    'sum': float(df[col].sum()) if hasattr(df[col], 'sum') else 0,
                                    'count': int(df[col].count()) if hasattr(df[col], 'count') else 0,
                                    'avg': float(df[col].mean()) if hasattr(df[col], 'mean') else 0
                                }
                            except:
                                pass
                        print(f"     Numeric summary: {sample_summary}")
                        analyzer.logger.info(f"Dataset {idx} ({name}): {row_count} rows - Numeric summary: {sample_summary}")
        print("="*60 + "\n")
        
        print("\nAnalyzing multi-dataset results...")
        analysis = analyzer.analyze_multi_data(datasets, question, mode=selected_option, session_id=session_id, stream=False)
        print(f"\nAnalysis Results (multi):\n{analysis}")

        if create_session:
            try:
                columns = list(primary_df.columns) if hasattr(primary_df, 'columns') else []
            except Exception:
                columns = []
            joined_sql = "\n\n".join([f"-- {i+1}. {item.get('name','Query')}\n{item.get('sql','')}" for i, item in enumerate(sql_items)])
            session_id = analyzer.conversation_manager.create_conversation(
                question=question,
                sql_query=joined_sql,
                query_result=primary_df,
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

        try:
            analyzer.last_sql_list = [d['sql'] for d in datasets]
            analyzer.last_result_sets = [{
                'name': d.get('name'),
                'purpose': d.get('purpose'),
                'rows': int(d['df'].shape[0]) if hasattr(d['df'], 'shape') else 0,
                'columns': list(d['df'].columns) if hasattr(d['df'], 'columns') else []
            } for d in datasets]
        except Exception:
            pass

        return primary_df, analysis, session_id
    except Exception as e:
        print(f"Error during analysis: {str(e)}")
        return None, f"Analysis error: {str(e)}", None


def ask_and_analyze_v2_stream(analyzer, question: str, create_session=True, selected_option=None,
                              has_chart=False, chart_spec=None, chart_data=None,
                              chart_type=None, chart_error=None, doc_file_url=None,
                              doc_filename=None, excel_file_url=None, excel_filename=None,
                              session_id=None, language=None):
    """流式版本的LLM-first multi-SQL flow: decompose -> Vanna SQL -> execute all -> stream analysis."""
    try:
        print(f"Question: {question}")

        # 生成SQL查询
        sql_items = generate_sql_multi_decompose(analyzer, question, mode=selected_option, session_id=session_id)
        if not sql_items:
            yield {'type': 'error', 'content': 'Unable to generate SQL for this question'}
            return

        # 执行SQL查询 - 并行执行以提高性能
        datasets = []
        def execute_single_query(item):
            """Execute a single SQL query and return result (thread-safe)"""
            sql_exec = (item.get('sql') or '').strip().rstrip(';')
            if not sql_exec:
                print(f"⚠️ Skipping empty SQL for {item.get('name', 'Query')}")
                return None
            print(f"Executing: {sql_exec[:100]}...")
            try:
                # Use thread connection for parallel execution
                df = analyzer.execute_sql(sql_exec, use_thread_connection=True)
                return {
                    'name': item.get('name') or 'Query',
                    'purpose': item.get('purpose') or '',
                    'sql': sql_exec,
                    'df': df
                }
            except Exception as e:
                print(f"❌ Error executing query '{item.get('name', 'Query')}': {e}")
                return None
        
        # Execute all queries in parallel (max 5 concurrent)
        with ThreadPoolExecutor(max_workers=5) as executor:
            future_to_item = {executor.submit(execute_single_query, item): item for item in sql_items}
            for future in as_completed(future_to_item):
                result = future.result()
                if result is not None:
                    # Check if DataFrame is empty
                    try:
                        df = result['df']
                        is_empty = df.empty if hasattr(df, 'empty') else (len(df) == 0 if hasattr(df, '__len__') else True)
                        if is_empty:
                            print(f"⚠️ Query '{result['name']}' returned empty result")
                        else:
                            print(f"✅ Query '{result['name']}' returned {len(df)} rows")
                    except Exception as e:
                        print(f"⚠️ Error checking DataFrame for '{result.get('name', 'Query')}': {e}")
                    datasets.append(result)

        # Check if we have any datasets at all
        if not datasets:
            print("❌ No valid datasets generated from SQL queries")
            yield {'type': 'error', 'content': 'No data found for the generated queries. All SQL queries either failed or returned no results.'}
            return

        # 发送执行进度
        yield {'type': 'execution_complete', 'datasets': len(datasets)}

        # 选择主要数据集
        primary_df = None
        all_empty = True
        for d in datasets:
            if d['df'] is not None:
                try:
                    is_empty = d['df'].empty if hasattr(d['df'], 'empty') else (len(d['df']) == 0 if hasattr(d['df'], '__len__') else True)
                    if not is_empty:
                        all_empty = False
                        if primary_df is None:
                            primary_df = d['df']
                except Exception:
                    pass
        
        if all_empty:
            print("⚠️ All queries returned empty results")
        
        if primary_df is None and datasets:
            # Use first dataset even if empty (for error reporting)
            primary_df = datasets[0]['df']

        print("\nStreaming multi-dataset analysis...")
        
        # 流式分析
        yield {'type': 'analysis_start'}
        
        accumulated_analysis = ''
        for token in analyzer.analyze_multi_data(datasets, question, mode=selected_option, session_id=session_id, stream=True):
            if token:
                accumulated_analysis += token
                yield {'type': 'analysis_token', 'content': token}
        
        yield {'type': 'analysis_complete', 'content': accumulated_analysis}

        # 保存会话
        if create_session:
            try:
                columns = list(primary_df.columns) if hasattr(primary_df, 'columns') else []
            except Exception:
                columns = []
            joined_sql = "\n\n".join([f"-- {i+1}. {item.get('name','Query')}\n{item.get('sql','')}" for i, item in enumerate(sql_items)])
            session_id = analyzer.conversation_manager.create_conversation(
                question=question,
                sql_query=joined_sql,
                query_result=primary_df,
                columns=columns,
                analysis=accumulated_analysis,
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
            yield {'type': 'session_saved', 'session_id': session_id}

        # 保存分析器状态
        try:
            analyzer.last_sql_list = [d['sql'] for d in datasets]
            analyzer.last_result_sets = [{
                'name': d.get('name'),
                'purpose': d.get('purpose'),
                'rows': int(d['df'].shape[0]) if hasattr(d['df'], 'shape') else 0,
                'columns': list(d['df'].columns) if hasattr(d['df'], 'columns') else []
            } for d in datasets]
        except Exception:
            pass

        yield {'type': 'complete'}
        
    except Exception as e:
        print(f"Error during streaming analysis: {str(e)}")
        yield {'type': 'error', 'content': f"Analysis error: {str(e)}"}


def ask_followup_v2(question: str, conversation_id: str, user_id: str = 'test_user'):
    """处理后续问题的v2版本"""
    try:
        # 这里应该实现后续问题的处理逻辑
        # 目前返回一个简单的响应
        return {
            'answer': f'Follow-up response for: {question}',
            'conversation_id': conversation_id,
            'user_id': user_id
        }
    except Exception as e:
        return {
            'error': f'Follow-up processing failed: {str(e)}',
            'question': question,
            'conversation_id': conversation_id
        }