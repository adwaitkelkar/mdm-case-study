import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { listAssets, listEmployees } from '../api/client.js';

export default function HomePage() {
  const [counts, setCounts] = useState({ assets: null, employees: null });
  const [error, setError] = useState(null);

  useEffect(() => {
    Promise.all([listAssets(), listEmployees()])
      .then(([assets, employees]) => {
        setCounts({ assets: assets.length, employees: employees.length });
      })
      .catch((err) => setError(err.message));
  }, []);

  return (
    <section className="page">
      <h1>WorkWize MDM Device Sync</h1>
      <p className="page-subtitle">
        Import assigned devices from your MDM provider and manage synced assets and employees.
      </p>

      {error && <p className="banner banner-error">{error}</p>}

      <div className="stat-grid">
        <Link to="/assets" className="stat-card">
          <span className="stat-value">{counts.assets ?? '—'}</span>
          <span className="stat-label">Synced Assets</span>
        </Link>
        <Link to="/employees" className="stat-card">
          <span className="stat-value">{counts.employees ?? '—'}</span>
          <span className="stat-label">Employees</span>
        </Link>
      </div>
    </section>
  );
}
