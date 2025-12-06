# 用户统计和Usage页面设计

## 1. 系统概述

### 1.1 统计系统架构
```
┌─────────────────────────────────────────────────────────────┐
│                    Usage Statistics System                  │
├─────────────────────────────────────────────────────────────┤
│  Frontend (React)                                           │
│  ┌─────────────────┬─────────────────┬─────────────────┐    │
│  │  Usage Dashboard│  Charts & Graphs│  Export Reports │    │
│  │  (使用仪表板)    │  (图表统计)      │  (导出报告)      │    │
│  └─────────────────┴─────────────────┴─────────────────┘    │
│                           │                                 │
│                           ▼                                 │
│  ┌─────────────────────────────────────────────────────────┐│
│  │                 Analytics API                           ││
│  └─────────────────────────────────────────────────────────┘│
│                           │                                 │
│                           ▼                                 │
│  Backend (FastAPI)                                          │
│  ┌─────────────────┬─────────────────┬─────────────────┐    │
│  │  Stats Collector│  Analytics      │  Report         │    │
│  │  (数据收集器)    │  Service        │  Generator      │    │
│  │                 │  (分析服务)      │  (报告生成器)    │    │
│  └─────────────────┴─────────────────┴─────────────────┘    │
│                           │                                 │
│                           ▼                                 │
│  ┌─────────────────────────────────────────────────────────┐│
│  │              MySQL Database                             ││
│  │           (统计数据存储)                                 ││
│  └─────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘
```

### 1.2 统计数据类型
```typescript
interface UserStatistics {
  // 基础统计
  totalQuestions: number;           // 总提问次数
  totalAnswers: number;             // 总回答次数
  totalSessions: number;            // 总会话数
  totalExports: number;             // 总导出次数
  
  // 时间统计
  totalUsageTime: number;           // 总使用时长(分钟)
  averageSessionTime: number;       // 平均会话时长
  lastActiveDate: string;           // 最后活跃日期
  registrationDate: string;         // 注册日期
  
  // 内容统计
  totalCharacters: number;          // 总字符数
  averageQuestionLength: number;    // 平均问题长度
  averageAnswerLength: number;      // 平均回答长度
  
  // 导出统计
  exportsByFormat: {
    pdf: number;
    csv: number;
    txt: number;
    png: number;
  };
  
  // 活跃度统计
  dailyActivity: DailyActivity[];   // 每日活跃度
  weeklyActivity: WeeklyActivity[]; // 每周活跃度
  monthlyActivity: MonthlyActivity[]; // 每月活跃度
  
  // 热门话题
  topTopics: TopicStatistic[];      // 热门话题
  topKeywords: KeywordStatistic[];  // 热门关键词
}
```

## 2. 数据收集系统

### 2.1 统计数据模型
```python
# app/models/statistics.py
from sqlalchemy import Column, String, Integer, DateTime, Text, Float, JSON
from sqlalchemy.dialects.mysql import BIGINT
from app.core.database import Base
from datetime import datetime

class UserStatistics(Base):
    """用户统计主表"""
    __tablename__ = "user_statistics"
    
    id = Column(String(36), primary_key=True)
    user_id = Column(String(36), nullable=False, unique=True, index=True)
    
    # 基础统计
    total_questions = Column(Integer, default=0)
    total_answers = Column(Integer, default=0)
    total_sessions = Column(Integer, default=0)
    total_exports = Column(Integer, default=0)
    
    # 时间统计
    total_usage_time = Column(Integer, default=0)  # 分钟
    average_session_time = Column(Float, default=0.0)
    last_active_date = Column(DateTime)
    
    # 内容统计
    total_characters = Column(BIGINT, default=0)
    average_question_length = Column(Float, default=0.0)
    average_answer_length = Column(Float, default=0.0)
    
    # 导出统计
    exports_pdf = Column(Integer, default=0)
    exports_csv = Column(Integer, default=0)
    exports_txt = Column(Integer, default=0)
    exports_png = Column(Integer, default=0)
    
    # 扩展数据
    metadata = Column(JSON)
    
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

class DailyActivity(Base):
    """每日活跃度统计"""
    __tablename__ = "daily_activity"
    
    id = Column(String(36), primary_key=True)
    user_id = Column(String(36), nullable=False, index=True)
    date = Column(DateTime, nullable=False, index=True)
    
    questions_count = Column(Integer, default=0)
    answers_count = Column(Integer, default=0)
    sessions_count = Column(Integer, default=0)
    exports_count = Column(Integer, default=0)
    usage_time = Column(Integer, default=0)  # 分钟
    characters_count = Column(Integer, default=0)
    
    created_at = Column(DateTime, default=datetime.utcnow)

class SessionActivity(Base):
    """会话活跃度统计"""
    __tablename__ = "session_activity"
    
    id = Column(String(36), primary_key=True)
    user_id = Column(String(36), nullable=False, index=True)
    session_id = Column(String(36), nullable=False, index=True)
    
    start_time = Column(DateTime, nullable=False)
    end_time = Column(DateTime)
    duration = Column(Integer, default=0)  # 秒
    
    questions_count = Column(Integer, default=0)
    answers_count = Column(Integer, default=0)
    exports_count = Column(Integer, default=0)
    characters_count = Column(Integer, default=0)
    
    # 会话元数据
    topics = Column(JSON)  # 话题标签
    keywords = Column(JSON)  # 关键词
    
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

class UserActivity(Base):
    """用户活动记录"""
    __tablename__ = "user_activity"
    
    id = Column(String(36), primary_key=True)
    user_id = Column(String(36), nullable=False, index=True)
    session_id = Column(String(36), index=True)
    
    activity_type = Column(String(50), nullable=False)  # question, answer, export, login, logout
    activity_data = Column(JSON)  # 活动详细数据
    
    timestamp = Column(DateTime, default=datetime.utcnow, index=True)
    ip_address = Column(String(45))
    user_agent = Column(Text)

class TopicStatistics(Base):
    """话题统计"""
    __tablename__ = "topic_statistics"
    
    id = Column(String(36), primary_key=True)
    user_id = Column(String(36), nullable=False, index=True)
    topic = Column(String(200), nullable=False)
    
    count = Column(Integer, default=1)
    last_used = Column(DateTime, default=datetime.utcnow)
    
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
```

### 2.2 数据收集服务
```python
# app/services/statistics_service.py
from typing import Dict, List, Optional
from datetime import datetime, timedelta
import asyncio
from sqlalchemy import func, desc
from app.models.statistics import *
from app.core.database import get_db

class StatisticsCollector:
    """统计数据收集器"""
    
    async def record_question(self, user_id: str, session_id: str, question: str):
        """记录用户提问"""
        await self._record_activity(
            user_id=user_id,
            session_id=session_id,
            activity_type="question",
            activity_data={
                "content": question,
                "length": len(question)
            }
        )
        
        # 更新统计数据
        await self._update_user_statistics(user_id, {
            "total_questions": 1,
            "total_characters": len(question)
        })
        
        # 更新每日活跃度
        await self._update_daily_activity(user_id, {
            "questions_count": 1,
            "characters_count": len(question)
        })
    
    async def record_answer(self, user_id: str, session_id: str, answer: str):
        """记录AI回答"""
        await self._record_activity(
            user_id=user_id,
            session_id=session_id,
            activity_type="answer",
            activity_data={
                "content": answer,
                "length": len(answer)
            }
        )
        
        # 更新统计数据
        await self._update_user_statistics(user_id, {
            "total_answers": 1,
            "total_characters": len(answer)
        })
        
        # 更新每日活跃度
        await self._update_daily_activity(user_id, {
            "answers_count": 1,
            "characters_count": len(answer)
        })
    
    async def record_export(self, user_id: str, session_id: str, format: str):
        """记录文件导出"""
        await self._record_activity(
            user_id=user_id,
            session_id=session_id,
            activity_type="export",
            activity_data={
                "format": format
            }
        )
        
        # 更新统计数据
        update_data = {
            "total_exports": 1,
            f"exports_{format}": 1
        }
        await self._update_user_statistics(user_id, update_data)
        
        # 更新每日活跃度
        await self._update_daily_activity(user_id, {
            "exports_count": 1
        })
    
    async def record_session_start(self, user_id: str, session_id: str):
        """记录会话开始"""
        session_activity = SessionActivity(
            id=session_id,
            user_id=user_id,
            session_id=session_id,
            start_time=datetime.utcnow()
        )
        
        async with get_db() as db:
            db.add(session_activity)
            await db.commit()
        
        # 更新统计数据
        await self._update_user_statistics(user_id, {
            "total_sessions": 1
        })
        
        # 更新每日活跃度
        await self._update_daily_activity(user_id, {
            "sessions_count": 1
        })
    
    async def record_session_end(self, user_id: str, session_id: str):
        """记录会话结束"""
        async with get_db() as db:
            session_activity = await db.query(SessionActivity).filter(
                SessionActivity.session_id == session_id
            ).first()
            
            if session_activity:
                session_activity.end_time = datetime.utcnow()
                session_activity.duration = int(
                    (session_activity.end_time - session_activity.start_time).total_seconds()
                )
                await db.commit()
                
                # 更新使用时长统计
                usage_minutes = session_activity.duration // 60
                await self._update_user_statistics(user_id, {
                    "total_usage_time": usage_minutes
                })
                
                await self._update_daily_activity(user_id, {
                    "usage_time": usage_minutes
                })
    
    async def _record_activity(self, user_id: str, session_id: str, 
                              activity_type: str, activity_data: Dict):
        """记录用户活动"""
        activity = UserActivity(
            user_id=user_id,
            session_id=session_id,
            activity_type=activity_type,
            activity_data=activity_data,
            timestamp=datetime.utcnow()
        )
        
        async with get_db() as db:
            db.add(activity)
            await db.commit()
    
    async def _update_user_statistics(self, user_id: str, updates: Dict):
        """更新用户统计数据"""
        async with get_db() as db:
            stats = await db.query(UserStatistics).filter(
                UserStatistics.user_id == user_id
            ).first()
            
            if not stats:
                stats = UserStatistics(user_id=user_id)
                db.add(stats)
            
            for key, value in updates.items():
                current_value = getattr(stats, key, 0)
                setattr(stats, key, current_value + value)
            
            stats.last_active_date = datetime.utcnow()
            stats.updated_at = datetime.utcnow()
            
            await db.commit()
    
    async def _update_daily_activity(self, user_id: str, updates: Dict):
        """更新每日活跃度"""
        today = datetime.utcnow().date()
        
        async with get_db() as db:
            activity = await db.query(DailyActivity).filter(
                DailyActivity.user_id == user_id,
                func.date(DailyActivity.date) == today
            ).first()
            
            if not activity:
                activity = DailyActivity(
                    user_id=user_id,
                    date=datetime.utcnow()
                )
                db.add(activity)
            
            for key, value in updates.items():
                current_value = getattr(activity, key, 0)
                setattr(activity, key, current_value + value)
            
            await db.commit()

class AnalyticsService:
    """分析服务"""
    
    async def get_user_statistics(self, user_id: str) -> Dict:
        """获取用户统计数据"""
        async with get_db() as db:
            # 基础统计
            stats = await db.query(UserStatistics).filter(
                UserStatistics.user_id == user_id
            ).first()
            
            if not stats:
                return self._get_empty_statistics()
            
            # 每日活跃度 (最近30天)
            thirty_days_ago = datetime.utcnow() - timedelta(days=30)
            daily_activity = await db.query(DailyActivity).filter(
                DailyActivity.user_id == user_id,
                DailyActivity.date >= thirty_days_ago
            ).order_by(DailyActivity.date).all()
            
            # 热门话题
            top_topics = await db.query(TopicStatistics).filter(
                TopicStatistics.user_id == user_id
            ).order_by(desc(TopicStatistics.count)).limit(10).all()
            
            return {
                "basic_stats": {
                    "total_questions": stats.total_questions,
                    "total_answers": stats.total_answers,
                    "total_sessions": stats.total_sessions,
                    "total_exports": stats.total_exports,
                    "total_usage_time": stats.total_usage_time,
                    "total_characters": stats.total_characters,
                    "last_active_date": stats.last_active_date.isoformat() if stats.last_active_date else None,
                    "registration_date": stats.created_at.isoformat()
                },
                "export_stats": {
                    "pdf": stats.exports_pdf,
                    "csv": stats.exports_csv,
                    "txt": stats.exports_txt,
                    "png": stats.exports_png
                },
                "daily_activity": [
                    {
                        "date": activity.date.strftime("%Y-%m-%d"),
                        "questions": activity.questions_count,
                        "answers": activity.answers_count,
                        "sessions": activity.sessions_count,
                        "exports": activity.exports_count,
                        "usage_time": activity.usage_time
                    }
                    for activity in daily_activity
                ],
                "top_topics": [
                    {
                        "topic": topic.topic,
                        "count": topic.count,
                        "last_used": topic.last_used.isoformat()
                    }
                    for topic in top_topics
                ],
                "averages": {
                    "session_time": stats.average_session_time,
                    "question_length": stats.average_question_length,
                    "answer_length": stats.average_answer_length
                }
            }
    
    async def get_usage_trends(self, user_id: str, period: str = "week") -> Dict:
        """获取使用趋势"""
        async with get_db() as db:
            if period == "week":
                start_date = datetime.utcnow() - timedelta(days=7)
            elif period == "month":
                start_date = datetime.utcnow() - timedelta(days=30)
            elif period == "year":
                start_date = datetime.utcnow() - timedelta(days=365)
            else:
                start_date = datetime.utcnow() - timedelta(days=7)
            
            activities = await db.query(DailyActivity).filter(
                DailyActivity.user_id == user_id,
                DailyActivity.date >= start_date
            ).order_by(DailyActivity.date).all()
            
            return {
                "period": period,
                "data": [
                    {
                        "date": activity.date.strftime("%Y-%m-%d"),
                        "questions": activity.questions_count,
                        "answers": activity.answers_count,
                        "usage_time": activity.usage_time
                    }
                    for activity in activities
                ]
            }
    
    def _get_empty_statistics(self) -> Dict:
        """获取空统计数据"""
        return {
            "basic_stats": {
                "total_questions": 0,
                "total_answers": 0,
                "total_sessions": 0,
                "total_exports": 0,
                "total_usage_time": 0,
                "total_characters": 0,
                "last_active_date": None,
                "registration_date": datetime.utcnow().isoformat()
            },
            "export_stats": {
                "pdf": 0,
                "csv": 0,
                "txt": 0,
                "png": 0
            },
            "daily_activity": [],
            "top_topics": [],
            "averages": {
                "session_time": 0.0,
                "question_length": 0.0,
                "answer_length": 0.0
            }
        }
```

## 3. Usage页面前端设计

### 3.1 Usage页面组件
```typescript
// pages/UsagePage.tsx
import React, { useState, useEffect } from 'react';
import { Card, Row, Col, Statistic, Select, DatePicker, Spin } from 'antd';
import { 
  MessageSquare, 
  FileText, 
  Clock, 
  TrendingUp,
  Download,
  Calendar,
  BarChart3,
  PieChart
} from 'lucide-react';
import { useAuth } from '../hooks/useAuth';
import { analyticsApi } from '../services/analytics';
import UsageChart from '../components/usage/UsageChart';
import ExportChart from '../components/usage/ExportChart';
import ActivityHeatmap from '../components/usage/ActivityHeatmap';
import TopTopics from '../components/usage/TopTopics';

const { Option } = Select;
const { RangePicker } = DatePicker;

const UsagePage: React.FC = () => {
  const { user } = useAuth();
  const [loading, setLoading] = useState(true);
  const [statistics, setStatistics] = useState(null);
  const [trends, setTrends] = useState(null);
  const [period, setPeriod] = useState('week');

  useEffect(() => {
    loadStatistics();
  }, []);

  useEffect(() => {
    loadTrends();
  }, [period]);

  const loadStatistics = async () => {
    try {
      setLoading(true);
      const data = await analyticsApi.getUserStatistics();
      setStatistics(data);
    } catch (error) {
      console.error('Failed to load statistics:', error);
    } finally {
      setLoading(false);
    }
  };

  const loadTrends = async () => {
    try {
      const data = await analyticsApi.getUsageTrends(period);
      setTrends(data);
    } catch (error) {
      console.error('Failed to load trends:', error);
    }
  };

  if (loading) {
    return (
      <div className="usage-page-loading">
        <Spin size="large" />
        <p>Loading usage statistics...</p>
      </div>
    );
  }

  const { basic_stats, export_stats, daily_activity, top_topics } = statistics || {};

  return (
    <div className="usage-page">
      <div className="usage-header">
        <h1>Usage Statistics</h1>
        <p>Track your AI chat activity and usage patterns</p>
      </div>

      {/* 基础统计卡片 */}
      <Row gutter={[16, 16]} className="stats-cards">
        <Col xs={24} sm={12} lg={6}>
          <Card>
            <Statistic
              title="Total Questions"
              value={basic_stats?.total_questions || 0}
              prefix={<MessageSquare size={20} />}
              valueStyle={{ color: '#3b82f6' }}
            />
          </Card>
        </Col>
        <Col xs={24} sm={12} lg={6}>
          <Card>
            <Statistic
              title="Total Sessions"
              value={basic_stats?.total_sessions || 0}
              prefix={<BarChart3 size={20} />}
              valueStyle={{ color: '#10b981' }}
            />
          </Card>
        </Col>
        <Col xs={24} sm={12} lg={6}>
          <Card>
            <Statistic
              title="Usage Time"
              value={basic_stats?.total_usage_time || 0}
              suffix="min"
              prefix={<Clock size={20} />}
              valueStyle={{ color: '#f59e0b' }}
            />
          </Card>
        </Col>
        <Col xs={24} sm={12} lg={6}>
          <Card>
            <Statistic
              title="Total Exports"
              value={basic_stats?.total_exports || 0}
              prefix={<Download size={20} />}
              valueStyle={{ color: '#ef4444' }}
            />
          </Card>
        </Col>
      </Row>

      {/* 趋势图表 */}
      <Row gutter={[16, 16]} className="charts-section">
        <Col xs={24} lg={16}>
          <Card 
            title="Usage Trends" 
            extra={
              <Select value={period} onChange={setPeriod} style={{ width: 120 }}>
                <Option value="week">Last Week</Option>
                <Option value="month">Last Month</Option>
                <Option value="year">Last Year</Option>
              </Select>
            }
          >
            <UsageChart data={trends?.data || []} />
          </Card>
        </Col>
        <Col xs={24} lg={8}>
          <Card title="Export Distribution">
            <ExportChart data={export_stats} />
          </Card>
        </Col>
      </Row>

      {/* 活跃度热力图 */}
      <Row gutter={[16, 16]}>
        <Col xs={24} lg={16}>
          <Card title="Activity Heatmap">
            <ActivityHeatmap data={daily_activity || []} />
          </Card>
        </Col>
        <Col xs={24} lg={8}>
          <Card title="Top Topics">
            <TopTopics data={top_topics || []} />
          </Card>
        </Col>
      </Row>

      {/* 详细统计 */}
      <Row gutter={[16, 16]} className="detailed-stats">
        <Col xs={24} lg={12}>
          <Card title="Average Statistics">
            <div className="avg-stats">
              <div className="avg-stat-item">
                <span className="label">Average Session Time:</span>
                <span className="value">{statistics?.averages?.session_time?.toFixed(1) || 0} min</span>
              </div>
              <div className="avg-stat-item">
                <span className="label">Average Question Length:</span>
                <span className="value">{statistics?.averages?.question_length?.toFixed(0) || 0} chars</span>
              </div>
              <div className="avg-stat-item">
                <span className="label">Average Answer Length:</span>
                <span className="value">{statistics?.averages?.answer_length?.toFixed(0) || 0} chars</span>
              </div>
            </div>
          </Card>
        </Col>
        <Col xs={24} lg={12}>
          <Card title="Account Information">
            <div className="account-info">
              <div className="info-item">
                <span className="label">Registration Date:</span>
                <span className="value">
                  {basic_stats?.registration_date ? 
                    new Date(basic_stats.registration_date).toLocaleDateString() : 
                    'N/A'
                  }
                </span>
              </div>
              <div className="info-item">
                <span className="label">Last Active:</span>
                <span className="value">
                  {basic_stats?.last_active_date ? 
                    new Date(basic_stats.last_active_date).toLocaleDateString() : 
                    'N/A'
                  }
                </span>
              </div>
              <div className="info-item">
                <span className="label">Total Characters:</span>
                <span className="value">{basic_stats?.total_characters?.toLocaleString() || 0}</span>
              </div>
            </div>
          </Card>
        </Col>
      </Row>
    </div>
  );
};

export default UsagePage;
```

### 3.2 使用趋势图表组件
```typescript
// components/usage/UsageChart.tsx
import React from 'react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

interface UsageChartProps {
  data: Array<{
    date: string;
    questions: number;
    answers: number;
    usage_time: number;
  }>;
}

const UsageChart: React.FC<UsageChartProps> = ({ data }) => {
  return (
    <ResponsiveContainer width="100%" height={300}>
      <LineChart data={data}>
        <CartesianGrid strokeDasharray="3 3" />
        <XAxis 
          dataKey="date" 
          tick={{ fontSize: 12 }}
          tickFormatter={(value) => new Date(value).toLocaleDateString()}
        />
        <YAxis tick={{ fontSize: 12 }} />
        <Tooltip 
          labelFormatter={(value) => new Date(value).toLocaleDateString()}
          formatter={(value, name) => [value, name === 'usage_time' ? 'Usage Time (min)' : name]}
        />
        <Legend />
        <Line 
          type="monotone" 
          dataKey="questions" 
          stroke="#3b82f6" 
          strokeWidth={2}
          name="Questions"
        />
        <Line 
          type="monotone" 
          dataKey="answers" 
          stroke="#10b981" 
          strokeWidth={2}
          name="Answers"
        />
        <Line 
          type="monotone" 
          dataKey="usage_time" 
          stroke="#f59e0b" 
          strokeWidth={2}
          name="Usage Time"
        />
      </LineChart>
    </ResponsiveContainer>
  );
};

export default UsageChart;
```

### 3.3 导出分布饼图组件
```typescript
// components/usage/ExportChart.tsx
import React from 'react';
import { PieChart, Pie, Cell, ResponsiveContainer, Tooltip, Legend } from 'recharts';

interface ExportChartProps {
  data: {
    pdf: number;
    csv: number;
    txt: number;
    png: number;
  };
}

const ExportChart: React.FC<ExportChartProps> = ({ data }) => {
  const chartData = [
    { name: 'PDF', value: data?.pdf || 0, color: '#ef4444' },
    { name: 'CSV', value: data?.csv || 0, color: '#10b981' },
    { name: 'TXT', value: data?.txt || 0, color: '#3b82f6' },
    { name: 'PNG', value: data?.png || 0, color: '#f59e0b' }
  ].filter(item => item.value > 0);

  if (chartData.length === 0) {
    return (
      <div className="empty-chart">
        <p>No export data available</p>
      </div>
    );
  }

  return (
    <ResponsiveContainer width="100%" height={250}>
      <PieChart>
        <Pie
          data={chartData}
          cx="50%"
          cy="50%"
          labelLine={false}
          label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
          outerRadius={80}
          fill="#8884d8"
          dataKey="value"
        >
          {chartData.map((entry, index) => (
            <Cell key={`cell-${index}`} fill={entry.color} />
          ))}
        </Pie>
        <Tooltip />
      </PieChart>
    </ResponsiveContainer>
  );
};

export default ExportChart;
```

### 3.4 活跃度热力图组件
```typescript
// components/usage/ActivityHeatmap.tsx
import React from 'react';
import { Tooltip } from 'antd';

interface ActivityHeatmapProps {
  data: Array<{
    date: string;
    questions: number;
    answers: number;
    usage_time: number;
  }>;
}

const ActivityHeatmap: React.FC<ActivityHeatmapProps> = ({ data }) => {
  const getIntensity = (value: number, max: number) => {
    if (max === 0) return 0;
    return Math.min(Math.max(value / max, 0.1), 1);
  };

  const maxQuestions = Math.max(...data.map(d => d.questions), 1);
  
  // 生成最近30天的日期
  const generateDates = () => {
    const dates = [];
    for (let i = 29; i >= 0; i--) {
      const date = new Date();
      date.setDate(date.getDate() - i);
      dates.push(date.toISOString().split('T')[0]);
    }
    return dates;
  };

  const allDates = generateDates();
  const dataMap = new Map(data.map(d => [d.date, d]));

  return (
    <div className="activity-heatmap">
      <div className="heatmap-grid">
        {allDates.map(date => {
          const dayData = dataMap.get(date) || { questions: 0, answers: 0, usage_time: 0 };
          const intensity = getIntensity(dayData.questions, maxQuestions);
          
          return (
            <Tooltip
              key={date}
              title={
                <div>
                  <div>Date: {new Date(date).toLocaleDateString()}</div>
                  <div>Questions: {dayData.questions}</div>
                  <div>Answers: {dayData.answers}</div>
                  <div>Usage: {dayData.usage_time} min</div>
                </div>
              }
            >
              <div
                className="heatmap-cell"
                style={{
                  backgroundColor: `rgba(59, 130, 246, ${intensity})`,
                  border: '1px solid #e2e8f0'
                }}
              />
            </Tooltip>
          );
        })}
      </div>
      <div className="heatmap-legend">
        <span>Less</span>
        <div className="legend-scale">
          {[0.2, 0.4, 0.6, 0.8, 1.0].map(opacity => (
            <div
              key={opacity}
              className="legend-cell"
              style={{ backgroundColor: `rgba(59, 130, 246, ${opacity})` }}
            />
          ))}
        </div>
        <span>More</span>
      </div>
    </div>
  );
};

export default ActivityHeatmap;
```

### 3.5 热门话题组件
```typescript
// components/usage/TopTopics.tsx
import React from 'react';
import { Tag } from 'antd';

interface TopTopicsProps {
  data: Array<{
    topic: string;
    count: number;
    last_used: string;
  }>;
}

const TopTopics: React.FC<TopTopicsProps> = ({ data }) => {
  if (data.length === 0) {
    return (
      <div className="empty-topics">
        <p>No topics data available</p>
      </div>
    );
  }

  const getTagColor = (index: number) => {
    const colors = ['blue', 'green', 'orange', 'red', 'purple', 'cyan', 'magenta', 'lime'];
    return colors[index % colors.length];
  };

  return (
    <div className="top-topics">
      {data.map((topic, index) => (
        <div key={topic.topic} className="topic-item">
          <div className="topic-header">
            <Tag color={getTagColor(index)} className="topic-tag">
              {topic.topic}
            </Tag>
            <span className="topic-count">{topic.count}</span>
          </div>
          <div className="topic-last-used">
            Last used: {new Date(topic.last_used).toLocaleDateString()}
          </div>
        </div>
      ))}
    </div>
  );
};

export default TopTopics;
```

## 4. API接口设计

### 4.1 统计API路由
```python
# app/routers/analytics.py
from fastapi import APIRouter, Depends, HTTPException, Query
from typing import Optional
from app.services.statistics_service import AnalyticsService
from app.core.auth import get_current_user
from app.models.user import User

router = APIRouter(prefix="/api/analytics", tags=["analytics"])
analytics_service = AnalyticsService()

@router.get("/statistics")
async def get_user_statistics(
    current_user: User = Depends(get_current_user)
):
    """获取用户统计数据"""
    try:
        statistics = await analytics_service.get_user_statistics(current_user.id)
        return {
            "success": True,
            "data": statistics
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@router.get("/trends")
async def get_usage_trends(
    period: str = Query("week", regex="^(week|month|year)$"),
    current_user: User = Depends(get_current_user)
):
    """获取使用趋势"""
    try:
        trends = await analytics_service.get_usage_trends(current_user.id, period)
        return {
            "success": True,
            "data": trends
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@router.get("/activity/{date}")
async def get_daily_activity(
    date: str,
    current_user: User = Depends(get_current_user)
):
    """获取指定日期的活动详情"""
    try:
        activity = await analytics_service.get_daily_activity(current_user.id, date)
        return {
            "success": True,
            "data": activity
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@router.get("/export-report")
async def export_usage_report(
    format: str = Query("pdf", regex="^(pdf|csv|xlsx)$"),
    period: str = Query("month", regex="^(week|month|year)$"),
    current_user: User = Depends(get_current_user)
):
    """导出使用报告"""
    try:
        report_url = await analytics_service.export_usage_report(
            current_user.id, format, period
        )
        return {
            "success": True,
            "data": {
                "download_url": report_url
            }
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
```

## 5. 样式设计

### 5.1 Usage页面样式
```css
/* styles/pages/UsagePage.css */
.usage-page {
  padding: 24px;
  background: #ffffff;
  min-height: 100vh;
}

.usage-header {
  margin-bottom: 32px;
  text-align: center;
}

.usage-header h1 {
  font-size: 32px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 8px;
}

.usage-header p {
  font-size: 16px;
  color: #64748b;
}

.stats-cards {
  margin-bottom: 32px;
}

.stats-cards .ant-card {
  border-radius: 12px;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
  transition: all 0.2s ease;
}

.stats-cards .ant-card:hover {
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
  transform: translateY(-2px);
}

.charts-section {
  margin-bottom: 32px;
}

.charts-section .ant-card {
  border-radius: 12px;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
}

.detailed-stats {
  margin-bottom: 32px;
}

.avg-stats, .account-info {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.avg-stat-item, .info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #f1f5f9;
}

.avg-stat-item:last-child, .info-item:last-child {
  border-bottom: none;
}

.avg-stat-item .label, .info-item .label {
  font-weight: 500;
  color: #64748b;
}

.avg-stat-item .value, .info-item .value {
  font-weight: 600;
  color: #1e293b;
}

.usage-page-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 400px;
  gap: 16px;
}

.empty-chart, .empty-topics {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 200px;
  color: #64748b;
}

/* 活跃度热力图样式 */
.activity-heatmap {
  padding: 16px 0;
}

.heatmap-grid {
  display: grid;
  grid-template-columns: repeat(10, 1fr);
  gap: 2px;
  margin-bottom: 16px;
}

.heatmap-cell {
  width: 12px;
  height: 12px;
  border-radius: 2px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.heatmap-cell:hover {
  transform: scale(1.2);
}

.heatmap-legend {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 12px;
  color: #64748b;
}

.legend-scale {
  display: flex;
  gap: 2px;
}

.legend-cell {
  width: 10px;
  height: 10px;
  border-radius: 1px;
}

/* 热门话题样式 */
.top-topics {
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-height: 300px;
  overflow-y: auto;
}

.topic-item {
  padding: 12px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #f8fafc;
  transition: all 0.2s ease;
}

.topic-item:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.topic-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}

.topic-tag {
  margin: 0;
}

.topic-count {
  font-weight: 600;
  color: #1e293b;
}

.topic-last-used {
  font-size: 12px;
  color: #64748b;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .usage-page {
    padding: 16px;
  }
  
  .usage-header h1 {
    font-size: 24px;
  }
  
  .heatmap-grid {
    grid-template-columns: repeat(7, 1fr);
  }
  
  .heatmap-cell {
    width: 10px;
    height: 10px;
  }
}
```

这个用户统计和Usage页面设计提供了完整的数据收集、分析和展示功能，包括实时统计、趋势分析、活跃度热力图和热门话题等，为用户提供全面的使用情况洞察。