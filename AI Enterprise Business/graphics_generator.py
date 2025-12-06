"""
Graphics Generator Module
Generates infographics and visual presentations based on AI analysis results
"""

from PIL import Image, ImageDraw, ImageFont
import matplotlib.pyplot as plt
import matplotlib.patches as patches
from matplotlib.patches import FancyBboxPatch
import numpy as np
import os
from datetime import datetime
import uuid
import json
from typing import Dict, List, Any, Optional

class GraphicsGenerator:
    def __init__(self):
        self.exports_dir = "exports"
        if not os.path.exists(self.exports_dir):
            os.makedirs(self.exports_dir)
        
        # Set matplotlib style
        plt.style.use('default')
        
    def create_infographic(self, data: List[Dict], analysis_text: str, title: str = "Business Analysis Infographic") -> str:
        """Create a comprehensive business infographic"""
        
        # Create figure with custom size
        fig, ax = plt.subplots(figsize=(16, 12))
        
        # Set background color
        fig.patch.set_facecolor('#f8f9fa')
        ax.set_facecolor('#f8f9fa')
        
        # Remove axes
        ax.set_xlim(0, 100)
        ax.set_ylim(0, 100)
        ax.axis('off')
        
        # Title section
        self._add_title_section(ax, title)
        
        # Key metrics section
        self._add_metrics_section(ax, data)
        
        # Analysis summary section
        self._add_analysis_section(ax, analysis_text)
        
        # Visual elements
        self._add_visual_elements(ax, data)
        
        # Save the infographic
        filename = f"graphics_infographic_{datetime.now().strftime('%Y%m%d_%H%M%S')}.png"
        filepath = os.path.join(self.exports_dir, filename)
        
        plt.tight_layout()
        plt.savefig(filepath, dpi=300, bbox_inches='tight', facecolor='#f8f9fa')
        plt.close()
        
        return filepath
    
    def create_dashboard_visual(self, data: List[Dict], analysis_text: str, dashboard_type: str = "sales") -> str:
        """Create a dashboard-style visual"""
        
        # Create subplots for different dashboard elements
        fig = plt.figure(figsize=(20, 12))
        
        # Create a grid layout
        gs = fig.add_gridspec(3, 4, hspace=0.3, wspace=0.3)
        
        # Title
        fig.suptitle(f'{dashboard_type.title()} Dashboard', fontsize=24, fontweight='bold', y=0.95)
        
        # Key metrics cards (top row)
        self._create_metric_cards(fig, gs[0, :], data)
        
        # Charts (middle and bottom rows)
        self._create_charts_section(fig, gs[1:, :], data, dashboard_type)
        
        # Save the dashboard
        filename = f"graphics_dashboard_{dashboard_type}_{datetime.now().strftime('%Y%m%d_%H%M%S')}.png"
        filepath = os.path.join(self.exports_dir, filename)
        
        plt.savefig(filepath, dpi=300, bbox_inches='tight')
        plt.close()
        
        return filepath
    
    def create_timeline_visual(self, data: List[Dict], analysis_text: str, timeline_type: str = "project") -> str:
        """Create a timeline infographic"""
        
        fig, ax = plt.subplots(figsize=(18, 10))
        
        # Set background
        fig.patch.set_facecolor('white')
        ax.set_facecolor('white')
        
        # Remove axes
        ax.set_xlim(0, 100)
        ax.set_ylim(0, 100)
        ax.axis('off')
        
        # Add timeline title
        ax.text(50, 95, f'{timeline_type.title()} Timeline', 
                fontsize=24, fontweight='bold', ha='center', va='center')
        
        # Create timeline elements
        self._add_timeline_elements(ax, data, timeline_type)
        
        # Save the timeline
        filename = f"graphics_timeline_{timeline_type}_{datetime.now().strftime('%Y%m%d_%H%M%S')}.png"
        filepath = os.path.join(self.exports_dir, filename)
        
        plt.savefig(filepath, dpi=300, bbox_inches='tight', facecolor='white')
        plt.close()
        
        return filepath
    
    def create_comparison_visual(self, data: List[Dict], analysis_text: str, comparison_type: str = "competitive") -> str:
        """Create a comparison infographic"""
        
        fig, ax = plt.subplots(figsize=(16, 12))
        
        # Set background
        fig.patch.set_facecolor('#f5f5f5')
        ax.set_facecolor('#f5f5f5')
        
        # Remove axes
        ax.set_xlim(0, 100)
        ax.set_ylim(0, 100)
        ax.axis('off')
        
        # Add title
        ax.text(50, 95, f'{comparison_type.title()} Comparison', 
                fontsize=24, fontweight='bold', ha='center', va='center')
        
        # Create comparison elements
        self._add_comparison_elements(ax, data, comparison_type)
        
        # Save the comparison
        filename = f"graphics_comparison_{comparison_type}_{datetime.now().strftime('%Y%m%d_%H%M%S')}.png"
        filepath = os.path.join(self.exports_dir, filename)
        
        plt.savefig(filepath, dpi=300, bbox_inches='tight', facecolor='#f5f5f5')
        plt.close()
        
        return filepath
    
    def _add_title_section(self, ax, title: str):
        """Add title section to infographic"""
        # Title background
        title_bg = FancyBboxPatch((5, 85), 90, 12, 
                                   boxstyle="round,pad=0.5", 
                                   facecolor='#2c3e50', 
                                   edgecolor='#34495e', 
                                   linewidth=2)
        ax.add_patch(title_bg)
        
        # Title text
        ax.text(50, 91, title, fontsize=20, fontweight='bold', 
                ha='center', va='center', color='white')
        
        # Subtitle
        ax.text(50, 87, f'Generated on {datetime.now().strftime("%B %d, %Y")}', 
                fontsize=12, ha='center', va='center', color='#ecf0f1')
    
    def _add_metrics_section(self, ax, data: List[Dict]):
        """Add key metrics section"""
        if not data:
            return
            
        # Calculate some basic metrics from the data
        total_records = len(data)
        
        # Sample metrics (can be customized based on actual data)
        metrics = [
            {"label": "Total Records", "value": str(total_records), "color": "#3498db"},
            {"label": "Analysis Date", "value": datetime.now().strftime("%m/%d"), "color": "#e74c3c"},
            {"label": "Data Quality", "value": "High", "color": "#27ae60"},
            {"label": "Confidence", "value": "95%", "color": "#f39c12"}
        ]
        
        # Create metric cards
        card_width = 20
        card_height = 8
        start_x = 10
        start_y = 70
        
        for i, metric in enumerate(metrics):
            x = start_x + (i * 25)
            y = start_y
            
            # Card background
            card_bg = FancyBboxPatch((x, y), card_width, card_height,
                                     boxstyle="round,pad=0.3",
                                     facecolor=metric["color"],
                                     alpha=0.8)
            ax.add_patch(card_bg)
            
            # Metric label
            ax.text(x + card_width/2, y + card_height - 1, metric["label"], 
                    fontsize=10, fontweight='bold', ha='center', va='center', color='white')
            
            # Metric value
            ax.text(x + card_width/2, y + card_height/2, metric["value"], 
                    fontsize=14, fontweight='bold', ha='center', va='center', color='white')
    
    def _add_analysis_section(self, ax, analysis_text: str):
        """Add analysis summary section"""
        # Section background
        analysis_bg = FancyBboxPatch((10, 45), 80, 20,
                                    boxstyle="round,pad=0.5",
                                    facecolor='white',
                                    edgecolor='#bdc3c7',
                                    linewidth=1)
        ax.add_patch(analysis_bg)
        
        # Section title
        ax.text(15, 62, 'Key Insights', fontsize=16, fontweight='bold', 
                ha='left', va='center', color='#2c3e50')
        
        # Analysis text (truncate if too long)
        max_length = 200
        display_text = analysis_text[:max_length] + "..." if len(analysis_text) > max_length else analysis_text
        
        # Split text into multiple lines
        lines = self._split_text_into_lines(display_text, 60)
        
        for i, line in enumerate(lines[:4]):  # Max 4 lines
            ax.text(15, 58 - (i * 2), line, fontsize=10, 
                    ha='left', va='center', color='#34495e')
    
    def _add_visual_elements(self, ax, data: List[Dict]):
        """Add decorative visual elements"""
        # Add some decorative shapes
        shapes = [
            {"x": 5, "y": 15, "width": 15, "height": 15, "color": "#3498db", "alpha": 0.3},
            {"x": 80, "y": 20, "width": 12, "height": 12, "color": "#e74c3c", "alpha": 0.3},
            {"x": 70, "y": 10, "width": 8, "height": 8, "color": "#27ae60", "alpha": 0.3}
        ]
        
        for shape in shapes:
            rect = plt.Rectangle((shape["x"], shape["y"]), 
                               shape["width"], shape["height"],
                               facecolor=shape["color"], 
                               alpha=shape["alpha"],
                               transform=ax.transData)
            ax.add_patch(rect)
    
    def _create_metric_cards(self, fig, grid_spec, data: List[Dict]):
        """Create metric cards for dashboard"""
        if not data:
            return
            
        # Calculate metrics from data
        total_records = len(data)
        
        # Sample metrics
        metrics = [
            {"title": "Total Records", "value": total_records, "change": "+5.2%", "color": "#3498db"},
            {"title": "Growth Rate", "value": "12.5%", "change": "+2.1%", "color": "#27ae60"},
            {"title": "Efficiency", "value": "89%", "change": "+1.3%", "color": "#f39c12"},
            {"title": "Quality Score", "value": "4.8/5", "change": "+0.2", "color": "#e74c3c"}
        ]
        
        for i, metric in enumerate(metrics):
            ax = fig.add_subplot(grid_spec[i])
            
            # Card background
            ax.set_xlim(0, 10)
            ax.set_ylim(0, 10)
            ax.axis('off')
            
            # Card color
            card_color = metric["color"]
            card_bg = FancyBboxPatch((1, 2), 8, 6,
                                     boxstyle="round,pad=0.3",
                                     facecolor=card_color,
                                     alpha=0.8)
            ax.add_patch(card_bg)
            
            # Title
            ax.text(5, 7, metric["title"], fontsize=12, fontweight='bold', 
                    ha='center', va='center', color='white')
            
            # Value
            ax.text(5, 5, str(metric["value"]), fontsize=16, fontweight='bold', 
                    ha='center', va='center', color='white')
            
            # Change
            ax.text(5, 3, metric["change"], fontsize=10, 
                    ha='center', va='center', color='white', alpha=0.9)
    
    def _create_charts_section(self, fig, grid_spec, data: List[Dict], chart_type: str):
        """Create charts section for dashboard"""
        # Create different chart types based on available data
        
        # Bar chart
        ax1 = fig.add_subplot(grid_spec[0, :2])
        self._create_sample_bar_chart(ax1, data, chart_type)
        
        # Line chart
        ax2 = fig.add_subplot(grid_spec[0, 2:])
        self._create_sample_line_chart(ax2, data, chart_type)
        
        # Pie chart
        ax3 = fig.add_subplot(grid_spec[1, :2])
        self._create_sample_pie_chart(ax3, data, chart_type)
        
        # Area chart
        ax4 = fig.add_subplot(grid_spec[1, 2:])
        self._create_sample_area_chart(ax4, data, chart_type)
    
    def _create_sample_bar_chart(self, ax, data: List[Dict], chart_type: str):
        """Create sample bar chart"""
        # Sample data for demonstration
        categories = ['Q1', 'Q2', 'Q3', 'Q4']
        values = [25, 40, 35, 50]
        
        bars = ax.bar(categories, values, color=['#3498db', '#e74c3c', '#f39c12', '#27ae60'])
        ax.set_title('Quarterly Performance', fontsize=14, fontweight='bold')
        ax.set_ylabel('Values')
        
        # Add value labels on bars
        for bar, value in zip(bars, values):
            height = bar.get_height()
            ax.text(bar.get_x() + bar.get_width()/2., height + 1,
                   f'{value}', ha='center', va='bottom')
    
    def _create_sample_line_chart(self, ax, data: List[Dict], chart_type: str):
        """Create sample line chart"""
        # Sample data
        months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']
        values = [20, 25, 30, 28, 35, 40]
        
        ax.plot(months, values, marker='o', linewidth=3, markersize=8, color='#e74c3c')
        ax.set_title('Monthly Trends', fontsize=14, fontweight='bold')
        ax.set_ylabel('Values')
        ax.grid(True, alpha=0.3)
    
    def _create_sample_pie_chart(self, ax, data: List[Dict], chart_type: str):
        """Create sample pie chart"""
        labels = ['Product A', 'Product B', 'Product C', 'Product D']
        sizes = [30, 25, 20, 25]
        colors = ['#3498db', '#e74c3c', '#f39c12', '#27ae60']
        
        wedges, texts, autotexts = ax.pie(sizes, labels=labels, colors=colors, 
                                          autopct='%1.1f%%', startangle=90)
        ax.set_title('Market Share', fontsize=14, fontweight='bold')
        
        # Enhance text appearance
        for autotext in autotexts:
            autotext.set_color('white')
            autotext.set_fontweight('bold')
    
    def _create_sample_area_chart(self, ax, data: List[Dict], chart_type: str):
        """Create sample area chart"""
        # Sample data
        x = np.arange(1, 13)
        y1 = np.random.randint(10, 30, size=12)
        y2 = np.random.randint(5, 20, size=12)
        
        ax.fill_between(x, y1, alpha=0.7, color='#3498db', label='Series 1')
        ax.fill_between(x, y2, alpha=0.7, color='#e74c3c', label='Series 2')
        ax.set_title('Cumulative Growth', fontsize=14, fontweight='bold')
        ax.set_xlabel('Period')
        ax.set_ylabel('Values')
        ax.legend()
        ax.grid(True, alpha=0.3)
    
    def _add_timeline_elements(self, ax, data: List[Dict], timeline_type: str):
        """Add timeline elements"""
        # Sample timeline events
        events = [
            {"date": "2024-01", "title": "Project Start", "description": "Initial planning phase"},
            {"date": "2024-03", "title": "Development", "description": "Core development work"},
            {"date": "2024-06", "title": "Testing", "description": "Quality assurance phase"},
            {"date": "2024-09", "title": "Launch", "description": "Product launch"}
        ]
        
        # Create timeline
        y_positions = [75, 60, 45, 30]
        colors = ['#3498db', '#e74c3c', '#f39c12', '#27ae60']
        
        for i, (event, y_pos, color) in enumerate(zip(events, y_positions, colors)):
            # Timeline dot
            circle = plt.Circle((20 + (i * 25), y_pos), 2, color=color, zorder=3)
            ax.add_patch(circle)
            
            # Timeline line
            if i < len(events) - 1:
                ax.plot([20 + (i * 25), 20 + ((i + 1) * 25)], [y_pos, y_positions[i + 1]], 
                       color='#bdc3c7', linewidth=2, zorder=1)
            
            # Event details
            ax.text(25 + (i * 25), y_pos + 5, event["date"], fontsize=10, 
                    fontweight='bold', ha='left', va='center', color=color)
            ax.text(25 + (i * 25), y_pos, event["title"], fontsize=12, 
                    fontweight='bold', ha='left', va='center')
            ax.text(25 + (i * 25), y_pos - 4, event["description"], fontsize=9, 
                    ha='left', va='center', color='#7f8c8d', wrap=True)
    
    def _add_comparison_elements(self, ax, data: List[Dict], comparison_type: str):
        """Add comparison elements"""
        # Sample comparison data
        comparisons = [
            {"item": "Feature A", "score1": 85, "score2": 75, "label1": "Product 1", "label2": "Product 2"},
            {"item": "Feature B", "score1": 90, "score2": 85, "label1": "Product 1", "label2": "Product 2"},
            {"item": "Feature C", "score1": 70, "score2": 95, "label1": "Product 1", "label2": "Product 2"},
            {"item": "Feature D", "score1": 95, "score2": 80, "label1": "Product 1", "label2": "Product 2"}
        ]
        
        # Create comparison bars
        y_positions = [75, 60, 45, 30]
        bar_height = 8
        
        for i, (comparison, y_pos) in enumerate(zip(comparisons, y_positions)):
            # Item label
            ax.text(10, y_pos + bar_height/2, comparison["item"], fontsize=11, 
                    fontweight='bold', ha='left', va='center')
            
            # Product 1 bar
            bar1_width = comparison["score1"] * 0.4
            bar1 = plt.Rectangle((25, y_pos), bar1_width, bar_height, 
                               facecolor='#3498db', alpha=0.8)
            ax.add_patch(bar1)
            ax.text(25 + bar1_width + 1, y_pos + bar_height/2, 
                   f'{comparison["score1"]}%', fontsize=10, ha='left', va='center')
            
            # Product 2 bar
            bar2_width = comparison["score2"] * 0.4
            bar2 = plt.Rectangle((25, y_pos - bar_height - 2), bar2_width, bar_height, 
                               facecolor='#e74c3c', alpha=0.8)
            ax.add_patch(bar2)
            ax.text(25 + bar2_width + 1, y_pos - bar_height/2 - 2, 
                   f'{comparison["score2"]}%', fontsize=10, ha='left', va='center')
        
        # Legend
        legend_elements = [
            plt.Rectangle((0, 0), 1, 1, facecolor='#3498db', alpha=0.8, label=comparisons[0]["label1"]),
            plt.Rectangle((0, 0), 1, 1, facecolor='#e74c3c', alpha=0.8, label=comparisons[0]["label2"])
        ]
        ax.legend(handles=legend_elements, loc='upper right', bbox_to_anchor=(0.95, 0.95))
    
    def _split_text_into_lines(self, text: str, max_chars_per_line: int) -> List[str]:
        """Split text into multiple lines"""
        words = text.split()
        lines = []
        current_line = ""
        
        for word in words:
            if len(current_line + " " + word) <= max_chars_per_line:
                current_line += (" " + word if current_line else word)
            else:
                if current_line:
                    lines.append(current_line)
                current_line = word
        
        if current_line:
            lines.append(current_line)
        
        return lines if lines else [text]
    
    def generate_graphics_by_type(self, graphics_type: str, data: List[Dict], 
                                 analysis_text: str, title: str = None) -> str:
        """Generate graphics based on type"""
        
        graphics_mapping = {
            'infographic': self.create_infographic,
            'dashboard': self.create_dashboard_visual,
            'timeline': self.create_timeline_visual,
            'comparison': self.create_comparison_visual,
            'sales': lambda data, analysis, title=None: self.create_dashboard_visual(data, analysis, 'sales'),
            'marketing': lambda data, analysis, title=None: self.create_infographic(data, analysis, 'Marketing Analysis Infographic'),
            'finance': lambda data, analysis, title=None: self.create_dashboard_visual(data, analysis, 'finance'),
            'hr': lambda data, analysis, title=None: self.create_infographic(data, analysis, 'HR Analysis Infographic')
        }
        
        if graphics_type in graphics_mapping:
            if title:
                return graphics_mapping[graphics_type](data, analysis_text, title)
            else:
                return graphics_mapping[graphics_type](data, analysis_text)
        else:
            # Default to infographic
            return self.create_infographic(data, analysis_text, title or "Business Analysis Infographic")