import Topbar from '../../components/Topbar'
import Sidebar from '../../components/Sidebar'

export default function Dashboard() {
  return (
    <div className="layout">
      <Topbar />
      <div className="content">
        <Sidebar />
        <main className="main">
          <h1>User</h1>
          <div className="card">
            <div className="card-title">Overview</div>
            <div className="card-body">Work items and profile</div>
          </div>
        </main>
      </div>
    </div>
  )
}

