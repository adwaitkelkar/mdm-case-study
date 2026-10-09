import { NavLink } from 'react-router-dom';

const linkClass = ({ isActive }) => `navbar-link${isActive ? ' active' : ''}`;

export default function NavBar() {
  return (
    <header className="navbar">
      <div className="navbar-inner">
        <span className="navbar-brand">WorkWize</span>
        <nav className="navbar-links">
          <NavLink to="/" end className={linkClass}>
            Home
          </NavLink>
          <NavLink to="/assets" className={linkClass}>
            Assets
          </NavLink>
          <NavLink to="/employees" className={linkClass}>
            Employees
          </NavLink>
        </nav>
      </div>
    </header>
  );
}
