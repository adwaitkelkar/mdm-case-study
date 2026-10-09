import { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { deleteAsset, getAsset } from '../api/client.js';

const ATTRIBUTE_LABELS = {
  model: 'Model',
  model_identifier: 'Model Identifier',
  processor: 'Processor',
  core_count: 'Core Count',
  ram_gb: 'RAM (GB)',
  storage_gb: 'Storage (GB)',
  battery_percent: 'Battery (%)',
  platform: 'Platform',
};

export default function AssetDetailPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [asset, setAsset] = useState(null);
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    setLoading(true);
    getAsset(id)
      .then(setAsset)
      .catch((err) => setError(err.message))
      .finally(() => setLoading(false));
  }, [id]);

  const handleDelete = async () => {
    if (!asset || !window.confirm(`Remove "${asset.device_name}" from the local database?`)) {
      return;
    }
    try {
      await deleteAsset(asset.id);
      navigate('/assets');
    } catch (err) {
      setError(err.message);
    }
  };

  return (
    <section className="page">
      <div className="page-header">
        <h1>Asset Details</h1>
        <Link to="/assets" className="btn btn-secondary">
          Back to Assets
        </Link>
      </div>

      {error && <p className="banner banner-error">{error}</p>}

      {loading ? (
        <p className="empty-state">Loading…</p>
      ) : !asset ? (
        <p className="empty-state">Asset not found.</p>
      ) : (
        <div className="panel detail-panel">
          <div className="detail-field">
            <span className="detail-label">Asset Name</span>
            <span className="detail-value">{asset.device_name}</span>
          </div>

          <div className="detail-field">
            <span className="detail-label">Serial Code</span>
            <span className="detail-value">{asset.serial_code}</span>
          </div>

          <div className="detail-field">
            <span className="detail-label">Assigned To</span>
            <span className="detail-value">
              {asset.employee ? `${asset.employee.name || '—'} (${asset.employee.email})` : 'Unassigned'}
            </span>
          </div>

          <div className="attributes-card">
            <h2>Asset Attributes</h2>
            {Object.keys(asset.attributes || {}).length === 0 ? (
              <p className="empty-state">No hardware attributes reported.</p>
            ) : (
              <dl className="attributes-grid">
                {Object.entries(asset.attributes).map(([key, value]) => (
                  <div key={key} className="attribute-row">
                    <dt>{ATTRIBUTE_LABELS[key] || key}</dt>
                    <dd>{String(value)}</dd>
                  </div>
                ))}
              </dl>
            )}
          </div>

          <button type="button" className="btn btn-danger-outline" onClick={handleDelete}>
            Delete Asset
          </button>
        </div>
      )}
    </section>
  );
}
