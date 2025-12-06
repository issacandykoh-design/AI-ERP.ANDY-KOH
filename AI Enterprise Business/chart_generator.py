#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Chart Generator for Craveva AI Enterprise Business
Generates Vega-Lite chart specifications based on data and chart type
"""

import json
import pandas as pd
from typing import Dict, List, Any, Optional

class ChartGenerator:
    """Generate Vega-Lite chart specifications for different chart types"""
    
    def __init__(self):
        self.chart_types = {
            'Line': self._generate_line_chart,
            'Bar': self._generate_bar_chart,
            'Pie': self._generate_pie_chart,
            'Area': self._generate_area_chart,
            'Point': self._generate_point_chart,
            'Curve': self._generate_curve_chart
        }
    
    def generate_chart(self, data: List[Dict], chart_type: str, title: str = None, 
                      x_field: str = None, y_field: str = None) -> Dict[str, Any]:
        """
        Generate a Vega-Lite chart specification
        
        Args:
            data: List of data records
            chart_type: Type of chart (Line, Bar, Pie, Area, Point)
            title: Chart title
            x_field: X-axis field name
            y_field: Y-axis field name
            
        Returns:
            Vega-Lite specification dictionary
        """
        if not data:
            return self._generate_empty_chart(title or "No Data Available")
        
        # Normalize chart type to title case
        chart_type_normalized = chart_type.title()
        
        if chart_type_normalized not in self.chart_types:
            raise ValueError(f"Unsupported chart type: {chart_type}. Supported types: {list(self.chart_types.keys())}")
        
        # Auto-detect fields if not provided
        if not x_field or not y_field:
            x_field, y_field = self._auto_detect_fields(data)

        # Build a smart, descriptive title if none provided
        final_title = title or self._build_smart_title(chart_type_normalized, x_field, y_field, data)

        return self.chart_types[chart_type_normalized](data, final_title, x_field, y_field)
    
    def _auto_detect_fields(self, data: List[Dict]) -> tuple:
        """Auto-detect appropriate X and Y fields from data"""
        if not data:
            return 'x', 'y'
        
        sample = data[0]
        fields = list(sample.keys())
        
        # Look for common field patterns
        x_field = None
        y_field = None
        
        # Common X-axis fields (categorical or time)
        x_candidates = ['date', 'time', 'month', 'year', 'category', 'product', 'name']
        for field in fields:
            if any(candidate in field.lower() for candidate in x_candidates):
                x_field = field
                break
        
        # Common Y-axis fields (numerical)
        y_candidates = ['amount', 'total', 'sales', 'revenue', 'count', 'quantity', 'price']
        for field in fields:
            if any(candidate in field.lower() for candidate in y_candidates):
                y_field = field
                break
        
        # Fallback to first two fields
        if not x_field:
            x_field = fields[0] if len(fields) > 0 else 'x'
        if not y_field:
            y_field = fields[1] if len(fields) > 1 else fields[0] if len(fields) > 0 else 'y'
        
        return x_field, y_field

    def _build_smart_title(self, chart_type: str, x_field: str, y_field: str, data: List[Dict]) -> str:
        """Construct a descriptive chart title based on fields, type, and data.
        The generated title is in English to ensure UI compatibility.
        """
        def pretty(s: str) -> str:
            return (s or '').replace('_', ' ').strip().title()

        def label_for_y(field: str) -> str:
            f = (field or '').lower()
            if 'revenue' in f:
                return 'Revenue'
            if 'sale' in f:
                return 'Sales'
            if 'amount' in f:
                return 'Amount'
            if 'total' in f:
                return 'Total'
            if 'count' in f or 'transactions' in f:
                return 'Count'
            if 'quantity' in f or 'qty' in f:
                return 'Quantity'
            if 'price' in f:
                return 'Price'
            if 'income' in f:
                return 'Income'
            return pretty(field)

        def label_for_x(field: str) -> str:
            f = (field or '').lower()
            if 'month' in f:
                return 'Month'
            if 'year' in f:
                return 'Year'
            if 'date' in f or 'time' in f:
                return 'Date'
            if 'product' in f:
                return 'Product'
            if 'category' in f:
                return 'Category'
            if 'name' in f:
                return 'Name'
            if 'payment' in f:
                return 'Payment Type'
            return pretty(field)

        def infer_time_range(values: List[Any]) -> Optional[str]:
            try:
                series = pd.to_datetime(pd.Series(values), errors='coerce', infer_datetime_format=True)
                series = series.dropna()
                if series.empty:
                    return None
                min_year = int(series.dt.year.min())
                max_year = int(series.dt.year.max())
                if min_year == max_year:
                    return str(min_year)
                return f"{min_year}\u2013{max_year}"
            except Exception:
                return None

        x_label = label_for_x(x_field)
        y_label = label_for_y(y_field)

        # Determine field types for wording
        x_type = self._get_field_type(data, x_field)

        # Optional time range suffix
        range_suffix = ''
        if x_type == 'temporal':
            try:
                x_values = [row.get(x_field) for row in (data or [])]
                rng = infer_time_range(x_values)
                if rng:
                    range_suffix = f" ({rng})"
            except Exception:
                pass

        # Compose title based on chart type
        if chart_type in ['Line', 'Area', 'Curve']:
            return f"{y_label} Over {x_label}{range_suffix}"
        if chart_type == 'Bar':
            return f"{y_label} by {x_label}{range_suffix}"
        if chart_type == 'Pie':
            return f"{y_label} Share by {x_label}"
        if chart_type == 'Point':
            return f"{y_label} vs {x_label}"
        # Fallback
        return f"{chart_type} – {y_label} by {x_label}{range_suffix}"
    
    def _generate_line_chart(self, data: List[Dict], title: str, x_field: str, y_field: str) -> Dict[str, Any]:
        """Generate line chart specification"""
        return {
            "$schema": "https://vega.github.io/schema/vega-lite/v5.json",
            "title": title or "Line Chart",
            "data": {"values": data},
            "mark": {"type": "line", "point": True, "tooltip": True},
            "encoding": {
                "x": {
                    "field": x_field,
                    "type": self._get_field_type(data, x_field),
                    "title": x_field.replace('_', ' ').title()
                },
                "y": {
                    "field": y_field,
                    "type": "quantitative",
                    "title": y_field.replace('_', ' ').title()
                },
                "color": {"value": "#1f77b4"}
            }
        }
    
    def _generate_bar_chart(self, data: List[Dict], title: str, x_field: str, y_field: str) -> Dict[str, Any]:
        """Generate bar chart specification"""
        return {
            "$schema": "https://vega.github.io/schema/vega-lite/v5.json",
            "title": title or "Bar Chart",
            "data": {"values": data},
            "mark": {"type": "bar", "tooltip": True},
            "encoding": {
                "x": {
                    "field": x_field,
                    "type": self._get_field_type(data, x_field),
                    "title": x_field.replace('_', ' ').title()
                },
                "y": {
                    "field": y_field,
                    "type": "quantitative",
                    "title": y_field.replace('_', ' ').title()
                },
                "color": {"value": "#ff7f0e"}
            }
        }
    
    def _generate_pie_chart(self, data: List[Dict], title: str, x_field: str, y_field: str) -> Dict[str, Any]:
        """Generate pie chart specification"""
        return {
            "$schema": "https://vega.github.io/schema/vega-lite/v5.json",
            "title": title or "Pie Chart",
            "data": {"values": data},
            "mark": {"type": "arc", "tooltip": True},
            "encoding": {
                "theta": {
                    "field": y_field,
                    "type": "quantitative"
                },
                "color": {
                    "field": x_field,
                    "type": "nominal",
                    "legend": {"title": x_field.replace('_', ' ').title()}
                }
            }
        }
    
    def _generate_area_chart(self, data: List[Dict], title: str, x_field: str, y_field: str) -> Dict[str, Any]:
        """Generate area chart specification"""
        return {
            "$schema": "https://vega.github.io/schema/vega-lite/v5.json",
            "title": title or "Area Chart",
            "data": {"values": data},
            "mark": {"type": "area", "tooltip": True},
            "encoding": {
                "x": {
                    "field": x_field,
                    "type": self._get_field_type(data, x_field),
                    "title": x_field.replace('_', ' ').title()
                },
                "y": {
                    "field": y_field,
                    "type": "quantitative",
                    "title": y_field.replace('_', ' ').title()
                },
                "color": {"value": "#2ca02c"}
            }
        }
    
    def _generate_point_chart(self, data: List[Dict], title: str, x_field: str, y_field: str) -> Dict[str, Any]:
        """Generate scatter plot (point chart) specification"""
        return {
            "$schema": "https://vega.github.io/schema/vega-lite/v5.json",
            "title": title or "Scatter Plot",
            "data": {"values": data},
            "mark": {"type": "circle", "size": 100, "tooltip": True},
            "encoding": {
                "x": {
                    "field": x_field,
                    "type": self._get_field_type(data, x_field),
                    "title": x_field.replace('_', ' ').title()
                },
                "y": {
                    "field": y_field,
                    "type": "quantitative",
                    "title": y_field.replace('_', ' ').title()
                },
                "color": {"value": "#d62728"}
            }
        }
    
    def _generate_curve_chart(self, data: List[Dict], title: str, x_field: str, y_field: str) -> Dict[str, Any]:
        """Generate curve chart specification with smooth interpolation"""
        return {
            "$schema": "https://vega.github.io/schema/vega-lite/v5.json",
            "title": title or "Curve Chart",
            "data": {"values": data},
            "mark": {
                "type": "line",
                "interpolate": "monotone",
                "point": True,
                "tooltip": True
            },
            "encoding": {
                "x": {
                    "field": x_field,
                    "type": self._get_field_type(data, x_field),
                    "title": x_field.replace('_', ' ').title()
                },
                "y": {
                    "field": y_field,
                    "type": "quantitative",
                    "title": y_field.replace('_', ' ').title()
                },
                "color": {"value": "#ff7f0e"}
            }
        }
    
    def _get_field_type(self, data: List[Dict], field: str) -> str:
        """Determine the Vega-Lite field type based on data"""
        if not data or field not in data[0]:
            return "nominal"
        
        sample_value = data[0][field]
        
        # Check if it's a number
        if isinstance(sample_value, (int, float)):
            return "quantitative"
        
        # Check if it's a date-like string
        if isinstance(sample_value, str):
            # Simple date pattern detection
            if any(char in sample_value for char in ['-', '/', ':']):
                return "temporal"
        
        return "nominal"
    
    def _generate_empty_chart(self, title: str) -> Dict[str, Any]:
        """Generate an empty chart when no data is available"""
        return {
            "$schema": "https://vega.github.io/schema/vega-lite/v5.json",
            "title": title,
            "mark": "text",
            "encoding": {
                "text": {"value": "No data available"}
            }
        }