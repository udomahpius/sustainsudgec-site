const express = require('express');
const cors = require('cors');
const fs = require('fs');
const path = require('path');
const PDFDocument = require('pdfkit');
const { v4: uuid } = require('uuid');

const app = express();
const PORT = process.env.PORT || 3000;

// ===== CORS =====
const corsOptions = {
  origin: ['https://sustainsudgecorg.org', 'http://127.0.0.1:5500'], 
  methods: ['GET', 'POST'],
  allowedHeaders: ['Content-Type']
};
app.use(cors(corsOptions));

// ===== Body Parser =====
app.use(express.json());

// ===== PDF Folder =====
app.use('/pdf', express.static(path.join(__dirname, 'pdf')));

app.post('/submit', (req, res) => {
  try {
    const data = req.body;
    const id = uuid();
    const pdfPath = path.join(__dirname, 'pdf', `${id}.pdf`);

    const doc = new PDFDocument({ size: 'A4', margin: 50 });
    doc.pipe(fs.createWriteStream(pdfPath));

    doc.fontSize(18).fillColor('#2c3e50').text('SUDGEC 2025 – Contractor Application', { align: 'center' });
    doc.moveDown();

    Object.entries(data).forEach(([key, value]) => {
      doc.fontSize(12).fillColor('#34495e').text(`${key.replace('_', ' ')}: ${value}`);
      doc.moveDown(0.5);
    });

    doc.end();

    // ===== Dynamic URL for local or live =====
    const protocol = req.protocol;
    const host = req.get('host');
    const pdfUrl = `${protocol}://${host}/pdf/${id}.pdf`;

    res.json({
      success: true,
      pdf_url: pdfUrl
    });

  } catch (err) {
    console.error(err);
    res.status(500).json({ success: false });
  }
});

app.listen(PORT, () => {
  console.log(`Backend running on ${PORT}`);
});
