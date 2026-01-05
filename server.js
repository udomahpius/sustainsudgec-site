require('dotenv').config(); // Add this at the top

const express = require('express');
const cors = require('cors');
const fs = require('fs');
const path = require('path');
const PDFDocument = require('pdfkit');
const { v4: uuid } = require('uuid');

const app = express();
const PORT = process.env.PORT || 3000;

// ===== 1. Setup CORS =====
const allowedOrigins = [
  'http://127.0.0.1:5500', // local testing
  'http://localhost:5500',
  process.env.BASE_URL          // live domain from .env
];
app.use(cors({
  origin: (origin, callback) => {
    if (!origin || allowedOrigins.includes(origin)) {
      callback(null, true);
    } else {
      callback(new Error('Not allowed by CORS'));
    }
  },
  methods: ['GET', 'POST'],
  allowedHeaders: ['Content-Type']
}));

// ===== 2. Middleware =====
app.use(express.json());
app.use('/pdf', express.static(path.join(__dirname, 'pdf')));

// Ensure PDF folder exists
const pdfFolder = path.join(__dirname, 'pdf');
if (!fs.existsSync(pdfFolder)) fs.mkdirSync(pdfFolder);

// ===== 3. Submit route =====
app.post('/submit', (req, res) => {
  try {
    const data = req.body;
    const id = uuid();
    const pdfPath = path.join(pdfFolder, `${id}.pdf`);

    const doc = new PDFDocument({ size: 'A4', margin: 50 });
    doc.pipe(fs.createWriteStream(pdfPath));

    // --- Add Title ---
    doc.fontSize(18).text('SUDGEC 2025 – Contractor Application', { align: 'center' });
    doc.moveDown();

    // --- Add Form Data ---
    Object.entries(data).forEach(([key, value]) => {
      doc.fontSize(12).text(`${key.replace('_', ' ')}: ${value}`);
      doc.moveDown(0.5);
    });

    doc.end();

    // --- Use BASE_URL from .env or fallback ---
    const baseURL = process.env.BASE_URL || `http://localhost:${PORT}`;

    res.json({
      success: true,
      pdf_url: `${baseURL}/pdf/${id}.pdf`
    });

  } catch (err) {
    console.error(err);
    res.status(500).json({ success: false, message: 'Server error' });
  }
});

// ===== 4. Start server =====
app.listen(PORT, () => {
  console.log(`Backend running on port ${PORT}`);
});
