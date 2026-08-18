import { useEffect, useMemo, useState } from 'react'
import { useNavigate, useSearchParams } from 'react-router-dom'
import api from '../api/client'
import { apiMessage, liters } from '../utils/format'

export default function Fueling() {
  const navigate = useNavigate()
  const [params] = useSearchParams()
  const [trucks, setTrucks] = useState([])
  const [truckId, setTruckId] = useState(params.get('truck') || '')
  const [quantity, setQuantity] = useState('')
  const [km, setKm] = useState('')
  const [limits, setLimits] = useState(null)
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    api.get('/trucks').then(({ data }) => setTrucks(data.data.filter((item) => item.status === 'ativo')))
  }, [])

  useEffect(() => {
    if (!truckId) { setLimits(null); return }
    api.get('/fuelings/limits', { params: { truck_id: truckId } }).then(({ data }) => {
      setLimits(data)
      setKm(data.truck.current_km)
    })
  }, [truckId])

  const qty = Number(quantity)
  const invalid = useMemo(() => {
    if (!limits || !qty) return ''
    if (qty > limits.max) {
      const reason = limits.truck_remaining < limits.tank_available ? 'capacidade restante' : 'saldo do tanque'
      if (reason === 'saldo do tanque') return `Saldo insuficiente. Disponível: ${liters(limits.tank_available)}.`
      return `Máximo permitido: ${liters(limits.max)} (${reason}).`
    }
    return ''
  }, [qty, limits])

  const projection = limits && qty > 0 && !invalid
    ? Math.min(limits.truck.tank_capacity, limits.truck.current_liters + qty)
    : null

  async function submit(event) {
    event.preventDefault()
    if (!truckId) { setError('Selecione um veículo para registrar o abastecimento.'); return }
    setError('')
    setLoading(true)
    try {
      const { data } = await api.post('/fuelings', {
        truck_id: Number(truckId),
        quantity: qty,
        current_km: km ? Number(km) : undefined,
      })
      navigate('/app/abastecer/confirmacao', { state: data.data })
    } catch (err) {
      setError(apiMessage(err, 'Não foi possível salvar. Verifique a conexão e tente novamente.'))
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="scroll">
      <p className="eyebrow">Operação</p>
      <h1>Abastecer caminhão</h1>
      <form className="stack" style={{ marginTop: 16 }} onSubmit={submit}>
        {(error || invalid) && <div className="banner banner-danger">{error || invalid}</div>}
        <div className="field">
          <label>Caminhão</label>
          <select value={truckId} onChange={(e) => setTruckId(e.target.value)} required>
            <option value="">Selecione</option>
            {trucks.map((truck) => (
              <option key={truck.id} value={truck.id}>{truck.plate} · {truck.model}</option>
            ))}
          </select>
        </div>
        {limits && (
          <div className="card muted">
            Tanque central: {liters(limits.tank_available)} · Restante no caminhão: {liters(limits.truck_remaining)}
          </div>
        )}
        <div className={`field ${invalid ? 'error' : ''}`}>
          <label>Quantidade (litros)</label>
          <input type="number" min="1" value={quantity} onChange={(e) => setQuantity(e.target.value)} required />
          {invalid && <span className="error-text">{invalid}</span>}
        </div>
        <div className="field">
          <label>KM atual (opcional)</label>
          <input type="number" min="0" value={km} onChange={(e) => setKm(e.target.value)} />
        </div>
        {projection !== null && (
          <div className="banner banner-ok">Projeção no caminhão: {liters(projection)}</div>
        )}
        <button className="btn btn-fuel" disabled={loading || Boolean(invalid) || !qty}>
          {loading ? 'Registrando...' : 'Confirmar abastecimento'}
        </button>
      </form>
    </div>
  )
}
