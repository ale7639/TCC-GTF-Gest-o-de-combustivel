import { useEffect, useState } from 'react'
import { Link, useOutletContext } from 'react-router-dom'
import api from '../api/client'
import TankGauge from '../components/TankGauge'
import { firstName, liters } from '../utils/format'
import { useAuth } from '../context/AuthContext'

export default function Dashboard() {
  const { user } = useAuth()
  const { unread } = useOutletContext()
  const [data, setData] = useState(null)

  useEffect(() => {
    api.get('/dashboard').then(({ data }) => setData(data))
  }, [])

  if (!data) return <div className="scroll"><p className="muted">Carregando painel...</p></div>

  return (
    <div className="scroll">
      <div className="topbar">
        <div>
          <p className="eyebrow">Operação de hoje</p>
          <h1>Olá, {firstName(user?.name)}</h1>
        </div>
        <Link to="/app/alertas" className="btn-icon" aria-label="Alertas">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <path d="M6 9a6 6 0 1 1 12 0c0 7 3 7 3 9H3c0-2 3-2 3-9" />
            <path d="M10 20a2 2 0 0 0 4 0" />
          </svg>
          {unread > 0 && <span className="bell-count">{unread}</span>}
        </Link>
      </div>

      <div className="stack">
        <TankGauge current={data.tank.current} percent={data.tank.percent} critical={data.tank.critical} />
        <div className="kpi">
          <div className="kpi-card">
            <div className="num">{data.fleet.ready}</div>
            <span>Caminhões prontos</span>
          </div>
          <div className="kpi-card">
            <div className="num">{data.fleet.pending}</div>
            <span>Pendente atenção</span>
          </div>
          <div className="kpi-card">
            <div className="num">{data.today.fuelings}</div>
            <span>Abastecimentos hoje</span>
          </div>
          <div className="kpi-card">
            <div className="num">{liters(data.today.liters)}</div>
            <span>Litros distribuídos</span>
          </div>
        </div>
        <Link to="/app/abastecer" className="btn btn-fuel">+ Abastecer caminhão</Link>
      </div>
    </div>
  )
}
