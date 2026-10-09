import { useCallback, useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { deleteAsset, listAssets, triggerSync } from '../api/client.js';

export default function AssetsPage() {
  const [assets, setAssets] = useState([]);
  const [loading, setLoading] = useState(true);
  const [syncing, setSyncing] = useState(false);
  const [error, setError] = useState(null);
  const [syncSummary, setSyncSummary] = useState(null);

  const load = useCallback(() => {
    setLoading(true);
    return listAssets()
      .then(setAssets)
      .catch((err) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  useEffect(() => {
    load();
  }, [load]);

  const handleSync = async () => {
    setSyncing(true);
    setError(null);
    setSyncSummary(null);
    try {
      const { result } = await triggerSync();
      setSyncSummary(result);
      await load();
    } catch (err) {
      setError(err.message);
    } finally {
      setSyncing(false);
    }
  };

  const handleDelete = async (asset) => {
    if (!window.confirm(`Remove "${asset.device_name}" from the local database?`)) {
      return;
    }
    try {
      await deleteAsset(asset.id);
      setAssets((current) => current.filter((item) => item.id !== asset.id));
    } catch (err) {
      setError(err.message);
    }
  };

  return (
    <section className="page">
      <div className="page-header">
        <h1>Asset Management</h1>
        <button type="button" className="btn btn-primary" onClick={handleSync} disabled={syncing}>
          {syncing ? 'Syncing…' : 'Sync Now'}
        </button>
      </div>

      {error && <p className="banner banner-error">{error}</p>}
      {syncSummary && (
        <p className="banner banner-success">
          Sync complete — {syncSummary.created_assets} created, {syncSummary.updated_assets} updated
          {syncSummary.skipped_unassigned > 0 ? `, ${syncSummary.skipped_unassigned} unassigned skipped` : ''}.
        </p>
      )}

      <div className="panel">
        {loading ? (
          <p className="empty-state">Loading assets…</p>
        ) : assets.length === 0 ? (
          <p className="empty-state">No assets yet. Click "Sync Now" to import devices from Jamf.</p>
        ) : (
          <ul className="list">
            {assets.map((asset) => (
              <li key={asset.id} className="list-row">
                <Link to={`/assets/${asset.id}`} className="list-row-main">
                  <span className="list-row-title">{asset.device_name}</span>
                  <span className="list-row-subtitle">
                    Assigned to: {asset.employee ? asset.employee.name || asset.employee.email : 'Unassigned'}
                  </span>
                </Link>
                <span className="list-row-meta">{asset.serial_code}</span>
                <button type="button" className="btn btn-danger-outline" onClick={() => handleDelete(asset)}>
                  Delete
                </button>
              </li>
            ))}
          </ul>
        )}
      </div>
    </section>
  );
}
