import Topbar from './components/Topbar'
import './index.css'

export default function App() {
  return (
    <div className="landing">
      <Topbar />
      <div className="landing-content">
        <h1>Panel Redesign Prototype</h1>
        <p>Select a role to preview.</p>
        <div className="links">
          <a href="/prototype/panelredesign/superadmin" className="btn">Superadmin</a>
          <a href="/prototype/panelredesign/admin" className="btn">Admin</a>
          <a href="/prototype/panelredesign/user" className="btn">User</a>
        </div>
      </div>
    </div>
  )
}
