#!/usr/bin/env python3
"""
Data Export Manager
Supports PDF report generation and CSV data export functionality
"""

import os
import csv
import pandas as pd
from datetime import datetime
from typing import List, Dict, Any, Optional
from reportlab.lib import colors
from reportlab.lib.pagesizes import letter, A4
from reportlab.platypus import SimpleDocTemplate, Table, TableStyle, Paragraph, Spacer, PageBreak
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import inch
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.lib.enums import TA_CENTER, TA_LEFT, TA_JUSTIFY


class ExportManager:
    """Data Export Manager"""
    
    def __init__(self, output_dir: str = "exports"):
        """
        Initialize export manager
        
        Args:
            output_dir: Output directory for exported files
        """
        self.output_dir = output_dir
        self.ensure_output_dir()
        self._register_chinese_fonts()
    
    def ensure_output_dir(self):
        """Ensure output directory exists"""
        if not os.path.exists(self.output_dir):
            os.makedirs(self.output_dir)
    
    def _register_chinese_fonts(self):
        """Register Chinese fonts for PDF generation"""
        try:
            # Try to register Chinese fonts from system
            import platform
            system = platform.system()
            
            if system == "Windows":
                # Windows system font paths
                font_paths = [
                    "C:/Windows/Fonts/simsun.ttc",  # SimSun
                    "C:/Windows/Fonts/simhei.ttf",  # SimHei
                    "C:/Windows/Fonts/msyh.ttc",    # Microsoft YaHei
                    "C:/Windows/Fonts/simkai.ttf",  # KaiTi
                ]
            elif system == "Darwin":  # macOS
                font_paths = [
                    "/System/Library/Fonts/PingFang.ttc",
                    "/System/Library/Fonts/STHeiti Light.ttc",
                    "/System/Library/Fonts/Hiragino Sans GB.ttc",
                ]
            else:  # Linux
                font_paths = [
                    "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf",
                    "/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf",
                    "/usr/share/fonts/opentype/noto/NotoSansCJK-Regular.ttc",
                ]
            
            # Try to register the first available font
            for font_path in font_paths:
                if os.path.exists(font_path):
                    try:
                        pdfmetrics.registerFont(TTFont('ChineseFont', font_path))
                        self.chinese_font = 'ChineseFont'
                        print(f"✅ Successfully registered Chinese font: {font_path}")
                        return
                    except Exception as e:
                        continue
            
            # If no system font found, use ReportLab built-in font
            print("⚠️ No Chinese system font found, using default font (may not support Chinese)")
            self.chinese_font = 'Helvetica'
            
        except Exception as e:
            print(f"⚠️ Font registration failed: {e}, using default font")
            self.chinese_font = 'Helvetica'
    
    def export_to_csv(self, data: List[Dict[str, Any]], filename: str = None) -> str:
        """
        Export data to CSV file
        
        Args:
            data: List of data to export
            filename: Filename (optional)
            
        Returns:
            str: Full path of exported file
        """
        if not data:
            raise ValueError("No data to export")
        
        # Generate filename
        if filename is None:
            timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
            filename = f"data_export_{timestamp}.csv"
        
        if not filename.endswith('.csv'):
            filename += '.csv'
        
        filepath = os.path.join(self.output_dir, filename)
        
        # Get all field names
        if isinstance(data[0], dict):
            fieldnames = list(data[0].keys())
        else:
            raise ValueError("Data must be a list of dictionaries")
        
        # Write CSV file
        with open(filepath, 'w', newline='', encoding='utf-8') as csvfile:
            writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
            writer.writeheader()
            writer.writerows(data)
        
        return filepath
    
    def export_to_pdf_report(self, 
                           question: str,
                           sql_query: str,
                           data: List[Dict[str, Any]],
                           analysis: str,
                           filename: str = None) -> str:
        """
        Generate PDF report
        
        Args:
            question: User question
            sql_query: Generated SQL query
            data: Query result data
            analysis: Analysis result
            filename: Filename (optional)
            
        Returns:
            str: Full path of exported file
        """
        # Generate filename
        if filename is None:
            timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
            filename = f"analysis_report_{timestamp}.pdf"
        
        if not filename.endswith('.pdf'):
            filename += '.pdf'
        
        filepath = os.path.join(self.output_dir, filename)
        
        # Create PDF document
        doc = SimpleDocTemplate(filepath, pagesize=A4)
        story = []
        
        # Get styles
        styles = getSampleStyleSheet()
        
        # Custom styles (using Chinese font)
        title_style = ParagraphStyle(
            'CustomTitle',
            parent=styles['Heading1'],
            fontName=self.chinese_font,
            fontSize=18,
            spaceAfter=30,
            alignment=TA_CENTER
        )
        
        heading_style = ParagraphStyle(
            'CustomHeading',
            parent=styles['Heading2'],
            fontName=self.chinese_font,
            fontSize=14,
            spaceAfter=12,
            spaceBefore=20
        )
        
        normal_style = ParagraphStyle(
            'CustomNormal',
            parent=styles['Normal'],
            fontName=self.chinese_font,
            fontSize=10,
            spaceAfter=12,
            alignment=TA_JUSTIFY
        )
        
        code_style = ParagraphStyle(
            'CustomCode',
            parent=styles['Code'],
            fontName='Courier',  # Use monospace font for code
            fontSize=9,
            spaceAfter=12,
            leftIndent=20,
            backgroundColor=colors.lightgrey
        )
        
        # Add title
        story.append(Paragraph("Craveva AI Enterprise Business Analysis Report", title_style))
        story.append(Spacer(1, 20))
        
        # Add generation time
        current_time = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        story.append(Paragraph(f"Generated: {current_time}", normal_style))
        story.append(Spacer(1, 20))
        
        # Add user question
        story.append(Paragraph("1. Analysis Question", heading_style))
        story.append(Paragraph(question, normal_style))
        story.append(Spacer(1, 12))
        
        # Add SQL query
        story.append(Paragraph("2. SQL Query", heading_style))
        sql_lines = sql_query.split('\n')
        for line in sql_lines:
            story.append(Paragraph(line, code_style))
        story.append(Spacer(1, 12))
        
        # Add data table
        story.append(Paragraph("3. Query Results", heading_style))
        
        if data:
            # Prepare table data
            table_data = []
            
            # Add header
            headers = list(data[0].keys())
            table_data.append(headers)
            
            # Add data rows
            for row in data[:20]:  # Limit to first 20 rows
                table_data.append([str(row.get(col, '')) for col in headers])
            
            # Create table
            table = Table(table_data)
            
            # Set table style
            table.setStyle(TableStyle([
                ('BACKGROUND', (0, 0), (-1, 0), colors.grey),
                ('TEXTCOLOR', (0, 0), (-1, 0), colors.whitesmoke),
                ('ALIGN', (0, 0), (-1, -1), 'CENTER'),
                ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
                ('FONTSIZE', (0, 0), (-1, 0), 10),
                ('BOTTOMPADDING', (0, 0), (-1, 0), 12),
                ('BACKGROUND', (0, 1), (-1, -1), colors.beige),
                ('FONTNAME', (0, 1), (-1, -1), 'Helvetica'),
                ('FONTSIZE', (0, 1), (-1, -1), 8),
                ('GRID', (0, 0), (-1, -1), 1, colors.black)
            ]))
            
            story.append(table)
        else:
            story.append(Paragraph("No query results", normal_style))
        
        story.append(Spacer(1, 20))
        
        # Add analysis results
        story.append(Paragraph("4. Data Analysis", heading_style))
        
        # Process analysis text, support line breaks
        analysis_lines = analysis.split('\n')
        for line in analysis_lines:
            if line.strip():
                story.append(Paragraph(line, normal_style))
        
        # Generate PDF
        doc.build(story)
        
        return filepath
    
    def export_single_response_to_pdf(self,
                                    session_id: str,
                                    question: str,
                                    response: str,
                                    timestamp: str,
                                    response_type: str,
                                    original_question: str = None,
                                    filename: str = None) -> str:
        """
        Export single AI response as PDF report
        
        Args:
            session_id: Session ID
            question: User question
            response: AI response content
            timestamp: Response timestamp
            response_type: Response type (initial/followup)
            original_question: Original question (for followup)
            filename: Filename (optional)
            
        Returns:
            str: Full path of exported file
        """
        # Generate filename
        if filename is None:
            clean_question = self._clean_text_for_filename(question)
            timestamp_str = timestamp[:19].replace(':', '-').replace('T', '_')
            filename = f"response_{clean_question}_{session_id[:8]}_{timestamp_str}.pdf"
        
        if not filename.endswith('.pdf'):
            filename += '.pdf'
        
        filepath = os.path.join(self.output_dir, filename)
        
        # Create PDF document
        doc = SimpleDocTemplate(filepath, pagesize=A4)
        story = []
        
        # Set styles (using Chinese font)
        styles = getSampleStyleSheet()
        title_style = ParagraphStyle(
            'CustomTitle',
            parent=styles['Heading1'],
            fontName=self.chinese_font,
            fontSize=16,
            spaceAfter=30,
            alignment=TA_CENTER,
            textColor=colors.darkblue
        )
        
        heading_style = ParagraphStyle(
            'CustomHeading',
            parent=styles['Heading2'],
            fontName=self.chinese_font,
            fontSize=12,
            spaceAfter=12,
            textColor=colors.darkgreen
        )
        
        content_style = ParagraphStyle(
            'CustomContent',
            parent=styles['Normal'],
            fontName=self.chinese_font,
            fontSize=10,
            spaceAfter=12,
            alignment=TA_JUSTIFY
        )
        
        # Title
        title = f"AI Response Record - {response_type.upper()}"
        story.append(Paragraph(title, title_style))
        story.append(Spacer(1, 20))
        
        # Basic information
        story.append(Paragraph("📋 Basic Information", heading_style))
        info_data = [
            ["Session ID", session_id],
            ["Response Type", "Initial Response" if response_type == "initial" else "Follow-up Response"],
            ["Time", timestamp[:19].replace('T', ' ')],
        ]
        
        if original_question:
            info_data.append(["Original Question", original_question])
        
        info_table = Table(info_data)
        info_table.setStyle(TableStyle([
            ('BACKGROUND', (0, 0), (0, -1), colors.lightgrey),
            ('FONTNAME', (0, 0), (-1, -1), 'Helvetica'),
            ('FONTSIZE', (0, 0), (-1, -1), 9),
            ('ALIGN', (0, 0), (-1, -1), 'LEFT'),
            ('VALIGN', (0, 0), (-1, -1), 'TOP'),
            ('GRID', (0, 0), (-1, -1), 1, colors.black)
        ]))
        
        story.append(info_table)
        story.append(Spacer(1, 20))
        
        # User question
        story.append(Paragraph("❓ User Question", heading_style))
        story.append(Paragraph(question, content_style))
        story.append(Spacer(1, 12))
        
        # AI response
        story.append(Paragraph("🤖 AI Response", heading_style))
        # Process long text, display in paragraphs
        response_lines = response.split('\n')
        for line in response_lines:
            if line.strip():
                story.append(Paragraph(line, content_style))
        
        # Footer information
        footer_style = ParagraphStyle(
            'Footer',
            parent=styles['Normal'],
            fontSize=8,
            textColor=colors.grey,
            alignment=TA_CENTER
        )
        
        story.append(Spacer(1, 30))
        story.append(Paragraph(f"Export Time: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}", footer_style))
        
        # Generate PDF
        doc.build(story)
        
        return filepath
    
    def export_single_response_to_txt(self,
                                    session_id: str,
                                    question: str,
                                    response: str,
                                    timestamp: str,
                                    response_type: str,
                                    original_question: str = None,
                                    filename: str = None) -> str:
        """
        Export single AI response as text file
        
        Args:
            session_id: Session ID
            question: User question
            response: AI response content
            timestamp: Response timestamp
            response_type: Response type (initial/followup)
            original_question: Original question (for followup)
            filename: Filename (optional)
            
        Returns:
            str: Full path of exported file
        """
        # Generate filename
        if filename is None:
            clean_question = self._clean_text_for_filename(question)
            timestamp_str = timestamp[:19].replace(':', '-').replace('T', '_')
            filename = f"response_{clean_question}_{session_id[:8]}_{timestamp_str}.txt"
        
        if not filename.endswith('.txt'):
            filename += '.txt'
        
        filepath = os.path.join(self.output_dir, filename)
        
        # Generate text content
        content = []
        content.append("=" * 60)
        content.append(f"AI Response Record - {response_type.upper()}")
        content.append("=" * 60)
        content.append("")
        content.append("📋 Basic Information:")
        content.append(f"   Session ID: {session_id}")
        content.append(f"   Response Type: {'Initial Response' if response_type == 'initial' else 'Follow-up Response'}")
        content.append(f"   Time: {timestamp[:19].replace('T', ' ')}")
        
        if original_question:
            content.append(f"   Original Question: {original_question}")
        
        content.append("")
        content.append("❓ User Question:")
        content.append(question)
        content.append("")
        content.append("🤖 AI Response:")
        content.append(response)
        content.append("")
        content.append("-" * 60)
        content.append(f"Export Time: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        
        # Write file
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write('\n'.join(content))
        
        return filepath
    
    def _clean_text_for_filename(self, text: str, max_length: int = 30) -> str:
        """
        Clean text for use as filename
        
        Args:
            text: Original text
            max_length: Maximum length
            
        Returns:
            str: Cleaned text
        """
        # Remove or replace characters not suitable for filenames
        import re
        cleaned = re.sub(r'[<>:"/\\|?*]', '_', text)
        cleaned = re.sub(r'\s+', '_', cleaned)
        
        # Truncate length
        if len(cleaned) > max_length:
            cleaned = cleaned[:max_length]
        
        return cleaned
    
    def get_export_summary(self, filepath: str) -> Dict[str, Any]:
        """
        Get export file summary information
        
        Args:
            filepath: File path
            
        Returns:
            Dict: File summary information
        """
        if not os.path.exists(filepath):
            return {"error": "File does not exist"}
        
        stat = os.stat(filepath)
        
        # Format file size
        size_bytes = stat.st_size
        if size_bytes < 1024:
            size_str = f"{size_bytes} B"
        elif size_bytes < 1024 * 1024:
            size_str = f"{size_bytes / 1024:.1f} KB"
        else:
            size_str = f"{size_bytes / (1024 * 1024):.1f} MB"
        
        return {
            "filepath": filepath,
            "filename": os.path.basename(filepath),
            "size": size_str,
            "size_bytes": size_bytes,
            "created_time": datetime.fromtimestamp(stat.st_ctime).strftime('%Y-%m-%d %H:%M:%S'),
            "modified_time": datetime.fromtimestamp(stat.st_mtime).strftime('%Y-%m-%d %H:%M:%S')
        }

    def export_to_pdf(self, export_data: Dict[str, Any], filename: Optional[str] = None) -> str:
        """Generate a PDF report from export_data with conversation history."""
        # Generate filename
        if filename is None:
            ts = datetime.now().strftime("%Y%m%d_%H%M%S")
            sid = str(export_data.get('session_id', 'session'))
            filename = f"conversation_{sid[:8]}_{ts}.pdf"
        if not filename.endswith('.pdf'):
            filename += '.pdf'
        filepath = os.path.join(self.output_dir, filename)
        
        # Create PDF document
        doc = SimpleDocTemplate(filepath, pagesize=A4)
        story = []
        
        # Styles
        styles = getSampleStyleSheet()
        title_style = ParagraphStyle(
            'ExportTitle', parent=styles['Heading1'], fontName=self.chinese_font,
            fontSize=18, spaceAfter=20, alignment=TA_CENTER
        )
        heading_style = ParagraphStyle(
            'ExportHeading', parent=styles['Heading2'], fontName=self.chinese_font,
            fontSize=12, spaceAfter=10
        )
        normal_style = ParagraphStyle(
            'ExportNormal', parent=styles['Normal'], fontName=self.chinese_font,
            fontSize=10, spaceAfter=6
        )
        
        # Title and meta
        story.append(Paragraph("Conversation Analysis Report", title_style))
        meta_lines = [
            f"Session ID: {export_data.get('session_id', '')}",
            f"Created At: {export_data.get('created_at', '')}",
            f"Language: {export_data.get('language', '')}"
        ]
        for line in meta_lines:
            story.append(Paragraph(line, normal_style))
        story.append(Spacer(1, 12))
        
        conversations = export_data.get('conversation_history', []) or []
        total = len(conversations)
        
        for idx, item in enumerate(conversations, start=1):
            section_title = "Main Conversation" if item.get('type') == 'main' else f"Follow-up #{idx if item.get('type') == 'followup' else idx}"
            story.append(Paragraph(section_title, heading_style))
            
            # Question
            story.append(Paragraph("Question:", heading_style))
            story.append(Paragraph(str(item.get('question', '') or ''), normal_style))
            story.append(Spacer(1, 6))
            
            # SQL
            sql_query = item.get('sql_query')
            if sql_query:
                story.append(Paragraph("SQL Query:", heading_style))
                story.append(Paragraph(str(sql_query), normal_style))
                story.append(Spacer(1, 6))
            
            # Data table
            data_list = item.get('data') or []
            columns = item.get('columns') or []
            
            # If columns missing, try to infer from first row
            if (not columns) and isinstance(data_list, list) and len(data_list) > 0 and isinstance(data_list[0], dict):
                columns = list(data_list[0].keys())
            
            if columns and isinstance(data_list, list) and len(data_list) > 0:
                table_data = [columns]
                max_rows = 200  # safety cap
                for row in data_list[:max_rows]:
                    table_data.append([str(row.get(col, '')) for col in columns])
                story.append(Paragraph("Data:", heading_style))
                table = Table(table_data)
                table.setStyle(TableStyle([
                    ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor('#f0f0f0')),
                    ('TEXTCOLOR', (0, 0), (-1, 0), colors.black),
                    ('ALIGN', (0, 0), (-1, -1), 'LEFT'),
                    ('FONTNAME', (0, 0), (-1, -1), self.chinese_font),
                    ('FONTSIZE', (0, 0), (-1, -1), 9),
                    ('GRID', (0, 0), (-1, -1), 0.25, colors.grey),
                    ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
                ]))
                story.append(table)
                story.append(Spacer(1, 8))
            
            # Analysis
            analysis_text = item.get('analysis') or ''
            if analysis_text:
                story.append(Paragraph("Analysis:", heading_style))
                for line in str(analysis_text).split('\n'):
                    if line.strip():
                        story.append(Paragraph(line.strip(), normal_style))
            
            # Page break between sections
            if idx < total:
                story.append(PageBreak())
        
        # Build PDF
        doc.build(story)
        return filepath

    # 新增：简单 TXT 导出，兼容 main.py 的调用
    def export_to_txt(self, text_content: str, filename: Optional[str] = None) -> str:
        """Write plain text content to a TXT file under output_dir."""
        if filename is None:
            ts = datetime.now().strftime('%Y%m%d_%H%M%S')
            filename = f"export_{ts}.txt"
        if not filename.endswith('.txt'):
            filename += '.txt'
        filepath = os.path.join(self.output_dir, filename)
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(text_content or '')
        return filepath

    def export_analysis_only_to_pdf(self, analysis_text: str, session_id: str = None, filename: Optional[str] = None) -> str:
        """
        Export only the AI analysis response to PDF without user questions and SQL queries.
        
        Args:
            analysis_text: The AI analysis text to export
            session_id: Session ID for filename generation
            filename: Optional custom filename
            
        Returns:
            str: Path to the generated PDF file
        """
        # Generate filename
        if filename is None:
            ts = datetime.now().strftime('%Y%m%d_%H%M%S')
            sid = str(session_id or 'response')
            filename = f"response_{sid[:8]}_{ts}.pdf"
        if not filename.endswith('.pdf'):
            filename += '.pdf'
        filepath = os.path.join(self.output_dir, filename)
        
        # Create PDF document
        doc = SimpleDocTemplate(filepath, pagesize=A4)
        story = []
        
        # Styles
        styles = getSampleStyleSheet()
        title_style = ParagraphStyle(
            'ResponseTitle', parent=styles['Heading1'], fontName=self.chinese_font,
            fontSize=16, spaceAfter=20, alignment=TA_CENTER
        )
        normal_style = ParagraphStyle(
            'ResponseNormal', parent=styles['Normal'], fontName=self.chinese_font,
            fontSize=11, spaceAfter=8, alignment=TA_JUSTIFY
        )
        
        # Title
        story.append(Paragraph('AI Analysis Report', title_style))
        
        # Add session info if available
        if session_id:
            meta_style = ParagraphStyle(
                'ResponseMeta', parent=styles['Normal'], fontName=self.chinese_font,
                fontSize=9, spaceAfter=12, alignment=TA_CENTER
            )
            story.append(Paragraph(f"Session ID: {session_id}", meta_style))
            story.append(Paragraph(f"Generated: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}", meta_style))
        
        story.append(Spacer(1, 12))
        
        # Add analysis content
        if analysis_text:
            # Split text into paragraphs and process each line
            for line in str(analysis_text).split('\n'):
                line = line.strip()
                if line:
                    # Handle different types of content formatting
                    if line.startswith('**') and line.endswith('**'):
                        # Bold headers
                        header_style = ParagraphStyle(
                            'ResponseHeader', parent=styles['Heading3'], fontName=self.chinese_font,
                            fontSize=12, spaceAfter=6, spaceBefore=6
                        )
                        story.append(Paragraph(line.strip('*'), header_style))
                    elif line.startswith('*') or line.startswith('-') or line.startswith('•'):
                        # Bullet points
                        bullet_style = ParagraphStyle(
                            'ResponseBullet', parent=styles['Normal'], fontName=self.chinese_font,
                            fontSize=11, spaceAfter=4, leftIndent=20
                        )
                        story.append(Paragraph(line, bullet_style))
                    else:
                        # Regular paragraphs
                        story.append(Paragraph(line, normal_style))
                else:
                    # Empty line - add small spacer
                    story.append(Spacer(1, 6))
        else:
            story.append(Paragraph('No analysis content available.', normal_style))
        
        # Build PDF
        doc.build(story)
        return filepath


class DataFormatter:
    """Data formatting utility class"""
    
    @staticmethod
    def format_data_for_export(data: List[tuple], columns: List[str]) -> List[Dict[str, Any]]:
        """
        Format query results as dictionary list
        
        Args:
            data: Query result tuple list
            columns: Column name list
            
        Returns:
            List[Dict]: Formatted data
        """
        formatted_data = []
        for row in data:
            row_dict = {}
            for i, col in enumerate(columns):
                if i < len(row):
                    value = row[i]
                    # Handle None values
                    if value is None:
                        value = ""
                    # Handle datetime objects
                    elif hasattr(value, 'strftime'):
                        value = value.strftime('%Y-%m-%d %H:%M:%S')
                    row_dict[col] = value
                else:
                    row_dict[col] = ""
            formatted_data.append(row_dict)
        return formatted_data
    
    @staticmethod
    def clean_filename(filename: str) -> str:
        """
        Clean filename, remove illegal characters
        
        Args:
            filename: Original filename
            
        Returns:
            str: Cleaned filename
        """
        # Remove or replace illegal characters
        import re
        filename = re.sub(r'[<>:"/\\|?*]', '_', filename)
        filename = re.sub(r'\s+', '_', filename)
        
        # Limit length
        if len(filename) > 100:
            filename = filename[:100]
        
        return filename
    


    # 新增：简单 TXT 导出，兼容 main.py 的调用
    def export_to_txt(self, text_content: str, filename: Optional[str] = None) -> str:
        """Write plain text content to a TXT file under output_dir."""
        if filename is None:
            ts = datetime.now().strftime("%Y%m%d_%H%M%S")
            filename = f"export_{ts}.txt"
        if not filename.endswith('.txt'):
            filename += '.txt'
        filepath = os.path.join(self.output_dir, filename)
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(text_content or '')
        return filepath

    def export_analysis_only_to_pdf(self, analysis_text: str, session_id: str = None, filename: Optional[str] = None) -> str:
        """
        Export only the AI analysis response to PDF without user questions and SQL queries.
        
        Args:
            analysis_text: The AI analysis text to export
            session_id: Session ID for filename generation
            filename: Optional custom filename
            
        Returns:
            str: Path to the generated PDF file
        """
        # Generate filename
        if filename is None:
            ts = datetime.now().strftime("%Y%m%d_%H%M%S")
            sid = str(session_id or 'response')
            filename = f"response_{sid[:8]}_{ts}.pdf"
        if not filename.endswith('.pdf'):
            filename += '.pdf'
        filepath = os.path.join(self.output_dir, filename)
        
        # Create PDF document
        doc = SimpleDocTemplate(filepath, pagesize=A4)
        story = []
        
        # Styles
        styles = getSampleStyleSheet()
        title_style = ParagraphStyle(
            'ResponseTitle', parent=styles['Heading1'], fontName=self.chinese_font,
            fontSize=16, spaceAfter=20, alignment=TA_CENTER
        )
        normal_style = ParagraphStyle(
            'ResponseNormal', parent=styles['Normal'], fontName=self.chinese_font,
            fontSize=11, spaceAfter=8, alignment=TA_JUSTIFY
        )
        
        # Title
        story.append(Paragraph("AI Analysis Report", title_style))
        
        # Add session info if available
        if session_id:
            meta_style = ParagraphStyle(
                'ResponseMeta', parent=styles['Normal'], fontName=self.chinese_font,
                fontSize=9, spaceAfter=12, alignment=TA_CENTER
            )
            story.append(Paragraph(f"Session ID: {session_id}", meta_style))
            story.append(Paragraph(f"Generated: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}", meta_style))
        
        story.append(Spacer(1, 12))
        
        # Add analysis content
        if analysis_text:
            # Split text into paragraphs and process each line
            for line in str(analysis_text).split('\n'):
                line = line.strip()
                if line:
                    # Handle different types of content formatting
                    if line.startswith('**') and line.endswith('**'):
                        # Bold headers
                        header_style = ParagraphStyle(
                            'ResponseHeader', parent=styles['Heading3'], fontName=self.chinese_font,
                            fontSize=12, spaceAfter=6, spaceBefore=6
                        )
                        story.append(Paragraph(line.strip('*'), header_style))
                    elif line.startswith('*') or line.startswith('-') or line.startswith('•'):
                        # Bullet points
                        bullet_style = ParagraphStyle(
                            'ResponseBullet', parent=styles['Normal'], fontName=self.chinese_font,
                            fontSize=11, spaceAfter=4, leftIndent=20
                        )
                        story.append(Paragraph(line, bullet_style))
                    else:
                        # Regular paragraphs
                        story.append(Paragraph(line, normal_style))
                else:
                    # Empty line - add small spacer
                    story.append(Spacer(1, 6))
        else:
            story.append(Paragraph("No analysis content available.", normal_style))
        
        # Build PDF
        doc.build(story)
        return filepath