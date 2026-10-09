import { useCallback, useEffect, useState } from 'react';
import { deleteEmployee, listEmployees } from '../api/client.js';

export default function EmployeesPage() {
  const [employees, setEmployees] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const load = useCallback(() => {
    setLoading(true);
    return listEmployees()
      .then(setEmployees)
      .catch((err) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  useEffect(() => {
    load();
  }, [load]);

  const handleDelete = async (employee) => {
    const assetNote = employee.assets_count
      ? ` This will also remove their ${employee.assets_count} synced asset(s).`
      : '';
    if (!window.confirm(`Remove "${employee.name || employee.email}" from the local database?${assetNote}`)) {
      return;
    }
    try {
      await deleteEmployee(employee.id);
      setEmployees((current) => current.filter((item) => item.id !== employee.id));
    } catch (err) {
      setError(err.message);
    }
  };

  return (
    <section className="page">
      <div className="page-header">
        <h1>Employees</h1>
      </div>

      {error && <p className="banner banner-error">{error}</p>}

      <div className="panel">
        {loading ? (
          <p className="empty-state">Loading employees…</p>
        ) : employees.length === 0 ? (
          <p className="empty-state">No employees yet. Sync assets from the Assets page to import them.</p>
        ) : (
          <ul className="list">
            {employees.map((employee) => (
              <li key={employee.id} className="list-row">
                <div className="list-row-main">
                  <span className="list-row-title">{employee.name || '—'}</span>
                  <span className="list-row-subtitle">{employee.email}</span>
                </div>
                <span className="list-row-meta">
                  {employee.assets_count} asset{employee.assets_count === 1 ? '' : 's'}
                </span>
                <button type="button" className="btn btn-danger-outline" onClick={() => handleDelete(employee)}>
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
