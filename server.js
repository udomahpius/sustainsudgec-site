require("dotenv").config();
const express = require("express");
const mysql = require("mysql2");
const multer = require("multer");
const path = require("path");
const cors = require("cors");
const fs = require("fs");
const PDFDocument = require("pdfkit");

const app = express();
const PORT = process.env.PORT || 8000;

const UPLOADS_DIR = process.env.UPLOADS_DIR || "uploads";
const PDFS_DIR = process.env.PDFS_DIR || "pdfs";

// ---------------- Middleware ----------------
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use("/uploads", express.static(path.join(__dirname, UPLOADS_DIR)));
app.use("/pdfs", express.static(path.join(__dirname, PDFS_DIR)));

// ---------------- CORS ----------------
const allowedOrigins = [process.env.FRONTEND_LOCAL, process.env.FRONTEND_LIVE];
app.use(cors({
  origin: (origin, callback) => {
    if (!origin || allowedOrigins.includes(origin)) return callback(null, true);
    callback(new Error('Not allowed by CORS'));
  }
}));

// ---------------- MySQL ----------------
const db = mysql.createConnection({
  host: process.env.DB_HOST,
  user: process.env.DB_USER,
  password: process.env.DB_PASS,
  database: process.env.DB_NAME,
  port: process.env.DB_PORT || 3306
});

db.connect(err => {
  if (err) {
    console.error("❌ MySQL connection error:", err.message);
    process.exit(1);
  }
  console.log("✅ Connected to MySQL");
});

// ---------------- Multer Upload ----------------
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    if (!fs.existsSync(UPLOADS_DIR)) fs.mkdirSync(UPLOADS_DIR);
    cb(null, UPLOADS_DIR);
  },
  filename: (req, file, cb) => {
    cb(null, Date.now() + "-" + file.originalname);
  }
});
const upload = multer({ storage });

// ---------------- Save Contractor ----------------
app.post("/save_registration", upload.array("documents[]"), async (req, res) => {
  try {
    const { company_name, company_address, contact_person, phone, email, category, contract_value, payment_details, signature, date } = req.body;

    const documents = (req.files || []).map(f => `/${UPLOADS_DIR}/${f.filename}`).join(", ");

    const sql = `
      INSERT INTO contractor_registrations
      (company_name, company_address, contact_person, phone, email,
       category, contract_value, payment_details, signature, date_created, documents)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `;

    db.query(sql, [company_name, company_address, contact_person, phone, email, category, contract_value, payment_details, signature, date, documents], (err, result) => {
      if (err) return res.status(500).json({ success: false, message: "Database insert failed" });

      // Generate PDF
      if (!fs.existsSync(PDFS_DIR)) fs.mkdirSync(PDFS_DIR);
      const pdfPath = path.join(PDFS_DIR, `receipt-${result.insertId}.pdf`);
      const doc = new PDFDocument();
      doc.pipe(fs.createWriteStream(pdfPath));
      doc.fontSize(20).text("SUDGEC Contractor Registration Receipt", { align: "center" });
      doc.moveDown();
      doc.fontSize(14).text(`Company: ${company_name}`);
      doc.text(`Contact: ${contact_person}`);
      doc.text(`Email: ${email}`);
      doc.text(`Phone: ${phone}`);
      doc.text(`Category: ${category}`);
      doc.text(`Contract Value: ${contract_value}`);
      doc.text(`Date: ${date}`);
      doc.end();

      res.json({
        success: true,
        record_id: result.insertId,
        pdf_url: `/${PDFS_DIR}/receipt-${result.insertId}.pdf`
      });
    });
  } catch (err) {
    console.error(err);
    res.status(500).json({ success: false, message: "Server error" });
  }
});

// ---------------- Start Server ----------------
app.listen(PORT, () => console.log(`🚀 Server running at http://localhost:${PORT}`));
