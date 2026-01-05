const express = require('express');
const cors = require('cors');
const fs = require('fs');
const path = require('path');
const PDFDocument = require('pdfkit');
const { v4: uuid } = require('uuid');

const app = express();
const PORT = 3000;

app.use(cors());
app.use(express.json());
app.use('/pdf', express.static(path.join(__dirname, 'pdf')));

app.post('/submit', (req, res) => {
  try {
    const data = req.body;
    const id = uuid();
    const pdfPath = path.join(__dirname, 'pdf', `${id}.pdf`);

    const doc = new PDFDocument({ size: 'A4', margin: 50 });
    doc.pipe(fs.createWriteStream(pdfPath));

    doc.fontSize(18).text('SUDGEC 2025 – Contractor Application', { align: 'center' });
    doc.moveDown();

    Object.entries(data).forEach(([key, value]) => {
      doc.fontSize(12).text(`${key.replace('_', ' ')}: ${value}`);
      doc.moveDown(0.5);
    });

    doc.end();

    res.json({
      success: true,
      pdf_url: `http://localhost:${PORT}/pdf/${id}.pdf`
    });

  } catch (err) {
    console.error(err);
    res.status(500).json({ success: false });
  }
});

app.listen(PORT, () => {
  console.log(`Backend running on http://localhost:${PORT}`);
});
