import React from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Routes, Route, Link } from 'react-router-dom';

function Home() {
  return (
    <div style={{ padding: 20 }}>
      <h2>React Starter</h2>
      <p>Integrated with Laravel Mix.</p>
      <nav>
        <Link to="/react">Home</Link> | <Link to="/react/about">About</Link>
      </nav>
    </div>
  );
}

function About() {
  return (
    <div style={{ padding: 20 }}>
      <h2>About</h2>
      <p>React router test route.</p>
    </div>
  );
}

function App() {
  return (
    <BrowserRouter basename="/react">
      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/about" element={<About />} />
      </Routes>
    </BrowserRouter>
  );
}

const el = document.getElementById('react-root');
if (el) {
  const root = createRoot(el);
  root.render(<App />);
}
