import { useEffect, useState } from 'react'
import api from '../api/client'

const icons = {
  manutencao_vencida: '🛠️',
  lavagem_atrasada: '🚿',
  combustivel_baixo: '⛽',
  documentacao_vencer: '📄',
}

export default function Alerts() {
  const [data, setData] = useState(null)

  async function load() {
    const { data } = await api.get('/alerts')
    setData(data)
  }

  useEffect(() => { load() }, [])

  async function read(id) {
    await api.post(`/alerts/${id}/read`)
    await load()
  }

  if (!data) return <div className="scroll"><p className="muted">Carregando alertas...</p></div>

  return (
    <div className="scroll">
      <div className="topbar">
        <h1>Alertas</h1>
        {data.unread > 0 && (
          <button className="btn btn-ghost" onClick={async () => { await api.post('/alerts/read-all'); await load() }}>
            Marcar todos
          </button>
        )}
      </div>
      <div className="list">
        {data.data.map((alert) => (
          <button key={alert.id} className="list-item" onClick={() => read(alert.id)} style={{ textAlign: 'left' }}>
            <div className="truck-thumb">{icons[alert.type] || '•'}</div>
            <div className="grow">
              <strong>{alert.title}</strong>
              <div className="muted">{alert.description}</div>
            </div>
            <span className="muted">{alert.relative}</span>
          </button>
        ))}
      </div>
    </div>
  )
}
