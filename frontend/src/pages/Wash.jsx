import { useEffect, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import api from '../api/client'
import { apiMessage } from '../utils/format'

export default function Wash() {
  const { id } = useParams()
  const navigate = useNavigate()
  const [data, setData] = useState(null)
  const [error, setError] = useState('')

  async function load() {
    const { data } = await api.get(`/trucks/${id}/washes`)
    setData(data)
  }

  useEffect(() => { load() }, [id])

  async function mark() {
    setError('')
    try {
      await api.post(`/trucks/${id}/washes`)
      await load()
    } catch (err) {
      setError(apiMessage(err, 'Selecione um veículo para registrar a lavagem.'))
    }
  }

  if (!data) return <div className="scroll"><p className="muted">Carregando lavagem...</p></div>

  return (
    <div className="scroll">
      <button className="btn btn-ghost" onClick={() => navigate(-1)}>Voltar</button>
      <h1>Controle de lavagem</h1>
      <p className="muted">{data.truck.plate} · frequência {data.truck.wash_frequency_days} dias</p>
      {error && <div className="banner banner-danger">{error}</div>}
      <div className="card" style={{ marginTop: 12 }}>
        <p><strong>Última lavagem</strong><br />{data.last ? new Date(data.last.washed_at).toLocaleString('pt-BR') : 'Nunca'}</p>
        <p className="muted">{data.days_since != null ? `Há ${data.days_since} dia(s)` : 'Sem registro'}</p>
        <p>Próxima: {data.next_due || '—'}</p>
      </div>
      <button className="btn btn-primary" style={{ marginTop: 16 }} onClick={mark}>Marcar como lavado</button>
    </div>
  )
}
