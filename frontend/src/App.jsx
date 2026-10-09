import { Route, Routes } from 'react-router-dom';
import NavBar from './components/NavBar.jsx';
import HomePage from './pages/HomePage.jsx';
import AssetsPage from './pages/AssetsPage.jsx';
import AssetDetailPage from './pages/AssetDetailPage.jsx';
import EmployeesPage from './pages/EmployeesPage.jsx';

export default function App() {
  return (
    <div className="app-shell">
      <NavBar />
      <main className="app-main">
        <Routes>
          <Route path="/" element={<HomePage />} />
          <Route path="/assets" element={<AssetsPage />} />
          <Route path="/assets/:id" element={<AssetDetailPage />} />
          <Route path="/employees" element={<EmployeesPage />} />
        </Routes>
      </main>
    </div>
  );
}
