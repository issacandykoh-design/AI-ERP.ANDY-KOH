import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import './index.css'
import App from './App.tsx'
import SuperAdminDashboard from './pages/superadmin/Dashboard.tsx'
import AdminDashboard from './pages/admin/Dashboard.tsx'
import UserDashboard from './pages/user/Dashboard.tsx'

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <BrowserRouter>
      <Routes>
        <Route path="/prototype/panelredesign" element={<App />} />
        <Route path="/prototype/panelredesign/superadmin" element={<SuperAdminDashboard />} />
        <Route path="/prototype/panelredesign/admin" element={<AdminDashboard />} />
        <Route path="/prototype/panelredesign/user" element={<UserDashboard />} />
        <Route path="*" element={<Navigate to="/prototype/panelredesign" replace />} />
      </Routes>
    </BrowserRouter>
  </StrictMode>,
)
