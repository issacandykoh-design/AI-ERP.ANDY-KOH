import { useMemo } from 'react'

function useBrandColor() {
  const color = useMemo(() => {
    const url = new URL(window.location.href)
    const fromQuery = url.searchParams.get('brand')
    if (fromQuery) return fromQuery
    const css = getComputedStyle(document.documentElement).getPropertyValue('--header_color').trim()
    if (css) return css
    return '#2563eb'
  }, [])
  return color
}

export default function Topbar() {
  const brand = useBrandColor()
  return (
    <div style={{ backgroundColor: brand }} className="topbar">
      <div className="container">
        <div className="title">Panel Redesign Prototype</div>
        <div className="actions">
          <a href="/prototype/panelredesign/superadmin" className="link">Superadmin</a>
          <a href="/prototype/panelredesign/admin" className="link">Admin</a>
          <a href="/prototype/panelredesign/user" className="link">User</a>
        </div>
      </div>
    </div>
  )
}

