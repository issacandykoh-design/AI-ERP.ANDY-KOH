# 多格式文件导出系统设计

## 1. 系统概述

### 1.1 导出系统架构
```
┌─────────────────────────────────────────────────────────────┐
│                    Export System Architecture               │
├─────────────────────────────────────────────────────────────┤
│  Frontend (React)                                           │
│  ┌─────────────────┬─────────────────┬─────────────────┐    │
│  │  Mode Selector  │  Export Modal   │  Progress Bar   │    │
│  │  (模式切换器)    │  (导出弹窗)      │  (进度条)        │    │
│  └─────────────────┴─────────────────┴─────────────────┘    │
│                           │                                 │
│                           ▼                                 │
│  ┌─────────────────────────────────────────────────────────┐│
│  │                 API Gateway                             ││
│  └─────────────────────────────────────────────────────────┘│
│                           │                                 │
│                           ▼                                 │
│  Backend (FastAPI)                                          │
│  ┌─────────────────┬─────────────────┬─────────────────┐    │
│  │  Export Router  │  Export Service │  File Generator │    │
│  │  (路由层)        │  (业务层)        │  (生成器)        │    │
│  └─────────────────┴─────────────────┴─────────────────┘    │
│                           │                                 │
│                           ▼                                 │
│  ┌─────────────────────────────────────────────────────────┐│
│  │                 File Storage                            ││
│  │              (文件存储系统)                              ││
│  └─────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘
```

### 1.2 支持的导出格式
```typescript
type ExportFormat = {
  pdf: {
    name: 'PDF Report';
    icon: 'FileText';
    description: 'Complete analysis report with charts';
    mimeType: 'application/pdf';
  };
  csv: {
    name: 'CSV Data';
    icon: 'Table';
    description: 'Raw data in spreadsheet format';
    mimeType: 'text/csv';
  };
  txt: {
    name: 'Text Summary';
    icon: 'FileText';
    description: 'Plain text analysis summary';
    mimeType: 'text/plain';
  };
  png: {
    name: 'Chart Image';
    icon: 'Image';
    description: 'Data visualization as image';
    mimeType: 'image/png';
  };
};
```

## 2. 前端模式切换器设计

### 2.1 ModeSelector组件
```typescript
// components/export/ModeSelector.tsx
interface ModeSelectorProps {
  currentMode: ExportMode;
  onModeChange: (mode: ExportMode) => void;
  disabled?: boolean;
}

const ModeSelector: React.FC<ModeSelectorProps> = ({
  currentMode,
  onModeChange,
  disabled = false
}) => {
  const modes = [
    {
      key: 'pdf',
      label: 'PDF Report',
      icon: <FileText size={16} />,
      color: '#ef4444',
      description: 'Complete analysis report'
    },
    {
      key: 'csv',
      label: 'CSV Data',
      icon: <Table size={16} />,
      color: '#10b981',
      description: 'Raw data export'
    },
    {
      key: 'txt',
      label: 'Text Summary',
      icon: <FileText size={16} />,
      color: '#3b82f6',
      description: 'Plain text summary'
    },
    {
      key: 'png',
      label: 'Chart Image',
      icon: <Image size={16} />,
      color: '#f59e0b',
      description: 'Data visualization'
    }
  ];

  return (
    <div className="mode-selector">
      {modes.map((mode) => (
        <button
          key={mode.key}
          className={`mode-button ${currentMode === mode.key ? 'active' : ''}`}
          onClick={() => onModeChange(mode.key as ExportMode)}
          disabled={disabled}
          title={mode.description}
        >
          <span className="mode-icon" style={{ color: mode.color }}>
            {mode.icon}
          </span>
          <span className="mode-label">{mode.label}</span>
        </button>
      ))}
    </div>
  );
};
```

### 2.2 样式设计
```css
/* styles/components/ModeSelector.css */
.mode-selector {
  display: flex;
  gap: 8px;
  padding: 12px 16px;
  background: #ffffff;
  border-bottom: 1px solid #e2e8f0;
  border-radius: 8px 8px 0 0;
}

.mode-button {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  background: #ffffff;
  color: #64748b;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.mode-button:hover {
  border-color: #3b82f6;
  background: #f8fafc;
  color: #1e293b;
}

.mode-button.active {
  border-color: #3b82f6;
  background: #dbeafe;
  color: #1e40af;
}

.mode-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.mode-icon {
  display: flex;
  align-items: center;
}

.mode-label {
  white-space: nowrap;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .mode-selector {
    flex-wrap: wrap;
    gap: 6px;
  }
  
  .mode-button {
    flex: 1;
    min-width: calc(50% - 3px);
    justify-content: center;
  }
}
```

## 3. 导出服务架构

### 3.1 后端API设计
```python
# app/routers/export.py
from fastapi import APIRouter, Depends, HTTPException, BackgroundTasks
from fastapi.responses import FileResponse
from typing import Optional
import uuid

router = APIRouter(prefix="/api/export", tags=["export"])

@router.post("/generate/{format}")
async def generate_export(
    format: ExportFormat,
    session_id: str,
    background_tasks: BackgroundTasks,
    current_user: User = Depends(get_current_user)
):
    """生成导出文件"""
    try:
        # 创建导出任务
        task_id = str(uuid.uuid4())
        export_task = ExportTask(
            id=task_id,
            user_id=current_user.id,
            session_id=session_id,
            format=format,
            status="pending"
        )
        
        # 添加后台任务
        background_tasks.add_task(
            process_export_task,
            export_task
        )
        
        return {
            "task_id": task_id,
            "status": "pending",
            "message": f"Export task created for {format} format"
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@router.get("/status/{task_id}")
async def get_export_status(
    task_id: str,
    current_user: User = Depends(get_current_user)
):
    """获取导出任务状态"""
    task = await ExportTask.get_by_id(task_id)
    if not task or task.user_id != current_user.id:
        raise HTTPException(status_code=404, detail="Task not found")
    
    return {
        "task_id": task_id,
        "status": task.status,
        "progress": task.progress,
        "file_url": task.file_url if task.status == "completed" else None,
        "error": task.error if task.status == "failed" else None
    }

@router.get("/download/{task_id}")
async def download_file(
    task_id: str,
    current_user: User = Depends(get_current_user)
):
    """下载导出文件"""
    task = await ExportTask.get_by_id(task_id)
    if not task or task.user_id != current_user.id:
        raise HTTPException(status_code=404, detail="Task not found")
    
    if task.status != "completed":
        raise HTTPException(status_code=400, detail="Export not completed")
    
    file_path = task.file_path
    if not os.path.exists(file_path):
        raise HTTPException(status_code=404, detail="File not found")
    
    return FileResponse(
        path=file_path,
        filename=task.filename,
        media_type=task.mime_type
    )
```

### 3.2 导出服务实现
```python
# app/services/export_service.py
from abc import ABC, abstractmethod
from typing import Dict, Any
import asyncio

class BaseExportGenerator(ABC):
    """导出生成器基类"""
    
    @abstractmethod
    async def generate(self, data: Dict[str, Any]) -> str:
        """生成导出文件，返回文件路径"""
        pass
    
    @abstractmethod
    def get_mime_type(self) -> str:
        """获取MIME类型"""
        pass
    
    @abstractmethod
    def get_file_extension(self) -> str:
        """获取文件扩展名"""
        pass

class PDFExportGenerator(BaseExportGenerator):
    """PDF导出生成器"""
    
    async def generate(self, data: Dict[str, Any]) -> str:
        from reportlab.lib.pagesizes import letter, A4
        from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table
        from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
        from reportlab.lib.units import inch
        from reportlab.pdfbase import pdfmetrics
        from reportlab.pdfbase.ttfonts import TTFont
        
        # 注册中文字体
        pdfmetrics.registerFont(TTFont('SimHei', 'fonts/SimHei.ttf'))
        
        filename = f"export_{data['session_id']}_{int(time.time())}.pdf"
        file_path = os.path.join(EXPORT_DIR, filename)
        
        doc = SimpleDocTemplate(file_path, pagesize=A4)
        story = []
        styles = getSampleStyleSheet()
        
        # 自定义样式
        title_style = ParagraphStyle(
            'CustomTitle',
            parent=styles['Heading1'],
            fontName='SimHei',
            fontSize=18,
            spaceAfter=30,
            alignment=1  # 居中
        )
        
        # 添加标题
        title = Paragraph("Data Analysis Report", title_style)
        story.append(title)
        story.append(Spacer(1, 20))
        
        # 添加会话信息
        session_info = f"""
        <b>Session ID:</b> {data['session_id']}<br/>
        <b>Generated:</b> {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}<br/>
        <b>User:</b> {data['user_name']}<br/>
        """
        story.append(Paragraph(session_info, styles['Normal']))
        story.append(Spacer(1, 20))
        
        # 添加对话内容
        for message in data['messages']:
            if message['role'] == 'user':
                story.append(Paragraph(f"<b>Question:</b> {message['content']}", styles['Normal']))
            else:
                story.append(Paragraph(f"<b>Analysis:</b> {message['content']}", styles['Normal']))
            story.append(Spacer(1, 12))
        
        # 添加数据表格
        if 'table_data' in data:
            table_data = data['table_data']
            table = Table(table_data)
            table.setStyle([
                ('BACKGROUND', (0, 0), (-1, 0), '#3b82f6'),
                ('TEXTCOLOR', (0, 0), (-1, 0), '#ffffff'),
                ('ALIGN', (0, 0), (-1, -1), 'CENTER'),
                ('FONTNAME', (0, 0), (-1, 0), 'SimHei'),
                ('FONTSIZE', (0, 0), (-1, 0), 12),
                ('BOTTOMPADDING', (0, 0), (-1, 0), 12),
                ('BACKGROUND', (0, 1), (-1, -1), '#f8fafc'),
                ('GRID', (0, 0), (-1, -1), 1, '#e2e8f0')
            ])
            story.append(table)
        
        doc.build(story)
        return file_path
    
    def get_mime_type(self) -> str:
        return "application/pdf"
    
    def get_file_extension(self) -> str:
        return ".pdf"

class CSVExportGenerator(BaseExportGenerator):
    """CSV导出生成器"""
    
    async def generate(self, data: Dict[str, Any]) -> str:
        import pandas as pd
        
        filename = f"export_{data['session_id']}_{int(time.time())}.csv"
        file_path = os.path.join(EXPORT_DIR, filename)
        
        # 转换数据为DataFrame
        if 'table_data' in data:
            df = pd.DataFrame(data['table_data'][1:], columns=data['table_data'][0])
        else:
            # 如果没有表格数据，导出对话记录
            messages_data = []
            for i, message in enumerate(data['messages']):
                messages_data.append({
                    'Index': i + 1,
                    'Role': message['role'],
                    'Content': message['content'],
                    'Timestamp': message.get('timestamp', '')
                })
            df = pd.DataFrame(messages_data)
        
        df.to_csv(file_path, index=False, encoding='utf-8-sig')
        return file_path
    
    def get_mime_type(self) -> str:
        return "text/csv"
    
    def get_file_extension(self) -> str:
        return ".csv"

class TXTExportGenerator(BaseExportGenerator):
    """TXT导出生成器"""
    
    async def generate(self, data: Dict[str, Any]) -> str:
        filename = f"export_{data['session_id']}_{int(time.time())}.txt"
        file_path = os.path.join(EXPORT_DIR, filename)
        
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write("Data Analysis Summary\n")
            f.write("=" * 50 + "\n\n")
            
            f.write(f"Session ID: {data['session_id']}\n")
            f.write(f"Generated: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
            f.write(f"User: {data['user_name']}\n\n")
            
            for i, message in enumerate(data['messages'], 1):
                if message['role'] == 'user':
                    f.write(f"Question {i}:\n{message['content']}\n\n")
                else:
                    f.write(f"Analysis {i}:\n{message['content']}\n\n")
                f.write("-" * 30 + "\n\n")
        
        return file_path
    
    def get_mime_type(self) -> str:
        return "text/plain"
    
    def get_file_extension(self) -> str:
        return ".txt"

class PNGExportGenerator(BaseExportGenerator):
    """PNG图表导出生成器"""
    
    async def generate(self, data: Dict[str, Any]) -> str:
        import matplotlib.pyplot as plt
        import matplotlib.font_manager as fm
        import seaborn as sns
        
        # 设置中文字体
        plt.rcParams['font.sans-serif'] = ['SimHei']
        plt.rcParams['axes.unicode_minus'] = False
        
        filename = f"export_{data['session_id']}_{int(time.time())}.png"
        file_path = os.path.join(EXPORT_DIR, filename)
        
        # 创建图表
        fig, axes = plt.subplots(2, 2, figsize=(12, 10))
        fig.suptitle('Data Analysis Visualization', fontsize=16, fontweight='bold')
        
        # 示例图表1：柱状图
        if 'chart_data' in data:
            chart_data = data['chart_data']
            axes[0, 0].bar(chart_data['labels'], chart_data['values'])
            axes[0, 0].set_title('Data Distribution')
            axes[0, 0].set_xlabel('Categories')
            axes[0, 0].set_ylabel('Values')
        
        # 示例图表2：折线图
        if 'trend_data' in data:
            trend_data = data['trend_data']
            axes[0, 1].plot(trend_data['x'], trend_data['y'], marker='o')
            axes[0, 1].set_title('Trend Analysis')
            axes[0, 1].set_xlabel('Time')
            axes[0, 1].set_ylabel('Value')
        
        # 示例图表3：饼图
        if 'pie_data' in data:
            pie_data = data['pie_data']
            axes[1, 0].pie(pie_data['values'], labels=pie_data['labels'], autopct='%1.1f%%')
            axes[1, 0].set_title('Proportion Analysis')
        
        # 示例图表4：散点图
        if 'scatter_data' in data:
            scatter_data = data['scatter_data']
            axes[1, 1].scatter(scatter_data['x'], scatter_data['y'])
            axes[1, 1].set_title('Correlation Analysis')
            axes[1, 1].set_xlabel('X Variable')
            axes[1, 1].set_ylabel('Y Variable')
        
        plt.tight_layout()
        plt.savefig(file_path, dpi=300, bbox_inches='tight')
        plt.close()
        
        return file_path
    
    def get_mime_type(self) -> str:
        return "image/png"
    
    def get_file_extension(self) -> str:
        return ".png"

class ExportService:
    """导出服务主类"""
    
    def __init__(self):
        self.generators = {
            'pdf': PDFExportGenerator(),
            'csv': CSVExportGenerator(),
            'txt': TXTExportGenerator(),
            'png': PNGExportGenerator()
        }
    
    async def process_export_task(self, task: ExportTask):
        """处理导出任务"""
        try:
            # 更新任务状态
            task.status = "processing"
            task.progress = 10
            await task.save()
            
            # 获取会话数据
            session_data = await self.get_session_data(task.session_id, task.user_id)
            task.progress = 30
            await task.save()
            
            # 生成文件
            generator = self.generators[task.format]
            file_path = await generator.generate(session_data)
            task.progress = 80
            await task.save()
            
            # 更新任务信息
            task.file_path = file_path
            task.filename = os.path.basename(file_path)
            task.mime_type = generator.get_mime_type()
            task.file_url = f"/api/export/download/{task.id}"
            task.status = "completed"
            task.progress = 100
            await task.save()
            
        except Exception as e:
            task.status = "failed"
            task.error = str(e)
            await task.save()
    
    async def get_session_data(self, session_id: str, user_id: str) -> Dict[str, Any]:
        """获取会话数据"""
        session = await ChatSession.get_by_id(session_id)
        if not session or session.user_id != user_id:
            raise ValueError("Session not found or access denied")
        
        messages = await ChatMessage.get_by_session_id(session_id)
        
        return {
            'session_id': session_id,
            'user_name': session.user.username,
            'messages': [
                {
                    'role': msg.role,
                    'content': msg.content,
                    'timestamp': msg.created_at.isoformat()
                }
                for msg in messages
            ],
            'table_data': session.analysis_data.get('table_data', []),
            'chart_data': session.analysis_data.get('chart_data', {}),
            'trend_data': session.analysis_data.get('trend_data', {}),
            'pie_data': session.analysis_data.get('pie_data', {}),
            'scatter_data': session.analysis_data.get('scatter_data', {})
        }
```

## 4. 前端导出流程

### 4.1 导出Hook
```typescript
// hooks/useExport.ts
import { useState, useCallback } from 'react';
import { exportApi } from '../services/export';

interface ExportState {
  isExporting: boolean;
  progress: number;
  error: string | null;
  downloadUrl: string | null;
}

export const useExport = () => {
  const [state, setState] = useState<ExportState>({
    isExporting: false,
    progress: 0,
    error: null,
    downloadUrl: null
  });

  const startExport = useCallback(async (
    format: ExportFormat,
    sessionId: string
  ) => {
    setState({
      isExporting: true,
      progress: 0,
      error: null,
      downloadUrl: null
    });

    try {
      // 创建导出任务
      const { task_id } = await exportApi.generateExport(format, sessionId);
      
      // 轮询任务状态
      const pollStatus = async () => {
        const status = await exportApi.getExportStatus(task_id);
        
        setState(prev => ({
          ...prev,
          progress: status.progress
        }));
        
        if (status.status === 'completed') {
          setState(prev => ({
            ...prev,
            isExporting: false,
            downloadUrl: status.file_url
          }));
        } else if (status.status === 'failed') {
          setState(prev => ({
            ...prev,
            isExporting: false,
            error: status.error
          }));
        } else {
          // 继续轮询
          setTimeout(pollStatus, 1000);
        }
      };
      
      pollStatus();
      
    } catch (error) {
      setState(prev => ({
        ...prev,
        isExporting: false,
        error: error.message
      }));
    }
  }, []);

  const downloadFile = useCallback(async (taskId: string) => {
    try {
      const blob = await exportApi.downloadFile(taskId);
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `export_${Date.now()}`;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(url);
      document.body.removeChild(a);
    } catch (error) {
      setState(prev => ({
        ...prev,
        error: error.message
      }));
    }
  }, []);

  const resetState = useCallback(() => {
    setState({
      isExporting: false,
      progress: 0,
      error: null,
      downloadUrl: null
    });
  }, []);

  return {
    ...state,
    startExport,
    downloadFile,
    resetState
  };
};
```

### 4.2 导出进度组件
```typescript
// components/export/ExportProgress.tsx
interface ExportProgressProps {
  isVisible: boolean;
  progress: number;
  format: ExportFormat;
  onCancel: () => void;
}

const ExportProgress: React.FC<ExportProgressProps> = ({
  isVisible,
  progress,
  format,
  onCancel
}) => {
  if (!isVisible) return null;

  const formatLabels = {
    pdf: 'PDF Report',
    csv: 'CSV Data',
    txt: 'Text Summary',
    png: 'Chart Image'
  };

  return (
    <div className="export-progress-overlay">
      <div className="export-progress-modal">
        <div className="progress-header">
          <h3>Exporting {formatLabels[format]}</h3>
          <button onClick={onCancel} className="cancel-button">
            <X size={20} />
          </button>
        </div>
        
        <div className="progress-content">
          <div className="progress-bar">
            <div 
              className="progress-fill"
              style={{ width: `${progress}%` }}
            />
          </div>
          <div className="progress-text">
            {progress}% Complete
          </div>
        </div>
        
        <div className="progress-status">
          {progress < 30 && "Preparing data..."}
          {progress >= 30 && progress < 80 && "Generating file..."}
          {progress >= 80 && "Finalizing..."}
        </div>
      </div>
    </div>
  );
};
```

## 5. 文件存储和管理

### 5.1 存储策略
```python
# app/core/storage.py
import os
import shutil
from datetime import datetime, timedelta
from typing import Optional

class FileStorageManager:
    """文件存储管理器"""
    
    def __init__(self, base_dir: str = "exports"):
        self.base_dir = base_dir
        self.ensure_directory_exists()
    
    def ensure_directory_exists(self):
        """确保存储目录存在"""
        os.makedirs(self.base_dir, exist_ok=True)
        
        # 创建按日期分组的子目录
        today = datetime.now().strftime("%Y-%m-%d")
        daily_dir = os.path.join(self.base_dir, today)
        os.makedirs(daily_dir, exist_ok=True)
    
    def get_file_path(self, filename: str) -> str:
        """获取文件完整路径"""
        today = datetime.now().strftime("%Y-%m-%d")
        return os.path.join(self.base_dir, today, filename)
    
    def cleanup_old_files(self, days: int = 7):
        """清理过期文件"""
        cutoff_date = datetime.now() - timedelta(days=days)
        
        for root, dirs, files in os.walk(self.base_dir):
            for dir_name in dirs:
                try:
                    dir_date = datetime.strptime(dir_name, "%Y-%m-%d")
                    if dir_date < cutoff_date:
                        dir_path = os.path.join(root, dir_name)
                        shutil.rmtree(dir_path)
                        print(f"Cleaned up directory: {dir_path}")
                except ValueError:
                    # 不是日期格式的目录，跳过
                    continue
    
    def get_file_size(self, file_path: str) -> int:
        """获取文件大小"""
        if os.path.exists(file_path):
            return os.path.getsize(file_path)
        return 0
    
    def delete_file(self, file_path: str) -> bool:
        """删除文件"""
        try:
            if os.path.exists(file_path):
                os.remove(file_path)
                return True
        except Exception as e:
            print(f"Error deleting file {file_path}: {e}")
        return False
```

### 5.2 数据库模型
```python
# app/models/export.py
from sqlalchemy import Column, String, Integer, DateTime, Text, Enum
from sqlalchemy.dialects.mysql import JSON
from app.core.database import Base
import enum

class ExportStatus(enum.Enum):
    PENDING = "pending"
    PROCESSING = "processing"
    COMPLETED = "completed"
    FAILED = "failed"

class ExportFormat(enum.Enum):
    PDF = "pdf"
    CSV = "csv"
    TXT = "txt"
    PNG = "png"

class ExportTask(Base):
    __tablename__ = "export_tasks"
    
    id = Column(String(36), primary_key=True)
    user_id = Column(String(36), nullable=False, index=True)
    session_id = Column(String(36), nullable=False, index=True)
    format = Column(Enum(ExportFormat), nullable=False)
    status = Column(Enum(ExportStatus), default=ExportStatus.PENDING)
    progress = Column(Integer, default=0)
    file_path = Column(String(500))
    filename = Column(String(255))
    mime_type = Column(String(100))
    file_url = Column(String(500))
    file_size = Column(Integer, default=0)
    error = Column(Text)
    metadata = Column(JSON)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    completed_at = Column(DateTime)
    
    def __repr__(self):
        return f"<ExportTask {self.id}: {self.format.value} - {self.status.value}>"
```

## 6. 安全和性能考虑

### 6.1 安全措施
```python
# 文件访问控制
@router.get("/download/{task_id}")
async def download_file(
    task_id: str,
    current_user: User = Depends(get_current_user)
):
    # 验证用户权限
    task = await ExportTask.get_by_id(task_id)
    if not task or task.user_id != current_user.id:
        raise HTTPException(status_code=403, detail="Access denied")
    
    # 验证文件存在性
    if not os.path.exists(task.file_path):
        raise HTTPException(status_code=404, detail="File not found")
    
    # 验证文件类型
    allowed_types = ['application/pdf', 'text/csv', 'text/plain', 'image/png']
    if task.mime_type not in allowed_types:
        raise HTTPException(status_code=400, detail="Invalid file type")
    
    return FileResponse(
        path=task.file_path,
        filename=task.filename,
        media_type=task.mime_type,
        headers={"Cache-Control": "no-cache"}
    )
```

### 6.2 性能优化
```python
# 异步任务处理
import asyncio
from concurrent.futures import ThreadPoolExecutor

class ExportService:
    def __init__(self):
        self.executor = ThreadPoolExecutor(max_workers=4)
    
    async def process_export_task(self, task: ExportTask):
        """异步处理导出任务"""
        loop = asyncio.get_event_loop()
        
        try:
            # 在线程池中执行CPU密集型任务
            file_path = await loop.run_in_executor(
                self.executor,
                self._generate_file_sync,
                task
            )
            
            # 更新任务状态
            task.file_path = file_path
            task.status = ExportStatus.COMPLETED
            await task.save()
            
        except Exception as e:
            task.status = ExportStatus.FAILED
            task.error = str(e)
            await task.save()
```

### 6.3 限流和配额
```python
# 导出限流
from fastapi_limiter.depends import RateLimiter

@router.post("/generate/{format}")
async def generate_export(
    format: ExportFormat,
    session_id: str,
    current_user: User = Depends(get_current_user),
    ratelimit: dict = Depends(RateLimiter(times=10, seconds=60))
):
    # 检查用户配额
    daily_exports = await ExportTask.count_daily_exports(current_user.id)
    if daily_exports >= current_user.daily_export_limit:
        raise HTTPException(
            status_code=429, 
            detail="Daily export limit exceeded"
        )
    
    # 处理导出请求
    # ...
```

这个多格式文件导出系统设计提供了完整的前后端解决方案，支持PDF、CSV、TXT和PNG四种格式的导出，具有良好的用户体验和系统性能。