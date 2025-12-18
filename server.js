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

// ---------------- Middleware ----------------
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Serve uploads and pdfs directories
app.use(`/${process.env.UPLOADS_DIR}`, express.static(path.join(__dirname, process.env.UPLOADS_DIR)));
app.use(`/${process.env.PDFS_DIR}`, express.static(path.join(__dirname, process.env.PDFS_DIR)));

// ---------------- CORS ----------------
const allowedOrigins = [process.env.FRONTEND_LOCAL, process.env.FRONTEND_LIVE];
app.use(cors({ origin: allowedOrigins }));

// ---------------- MySQL ----------------
const db = mysql.createConnection({
  host: process.env.DB_HOST,
  user: process.env.DB_USER,
  password: process.env.DB_PASSWORD,
  database: process.env.DB_NAME,
  port: process.env.DB_PORT || 3306,
  connectTimeout: 10000,
});

db.connect(err => {
  if (err) {
    console.error("❌ MySQL connection failed!");
    console.error("Error code:", err.code);
    console.error("Error message:", err.message);
    console.error("Host:", process.env.DB_HOST);
    console.error("User:", process.env.DB_USER);
    process.exit(1);
  } else {
    console.log("✅ Connected to MySQL successfully!");
  }
});

// ---------------- Multer Upload ----------------
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    const dir = process.env.UPLOADS_DIR;
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
    cb(null, dir);
  },
  filename: (req, file, cb) => {
    cb(null, Date.now() + "-" + file.originalname.replace(/\s+/g, "_"));
  }
});
const upload = multer({ storage });

// ---------------- Save Contractor ----------------
app.post("/save_registration", upload.array("documents[]"), (req, res) => {
  try {
    const {
      company_name,
      company_address,
      contact_person,
      phone,
      email,
      category,
      contract_value,
      payment_details,
      signature,
      date
    } = req.body;

    if (!company_name || !contact_person || !email) {
      return res.status(400).json({ success: false, message: "Missing required fields" });
    }

    const documents = (req.files || []).map(f => `/${process.env.UPLOADS_DIR}/${f.filename}`).join(", ");

    const sql = `
      INSERT INTO contractor_registrations
      (company_name, company_address, contact_person, phone, email,
       category, contract_value, payment_details, signature, date_created, documents)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `;

    db.query(sql, [
      company_name,
      company_address,
      contact_person,
      phone,
      email,
      category,
      contract_value,
      payment_details,
      signature,
      date,
      documents
    ], (err, result) => {
      if (err) {
        console.error("❌ DB insert error:", err.message);
        return res.status(500).json({ success: false, message: "Database insert failed" });
      }

      // Generate PDF receipt
      const pdfDir = process.env.PDFS_DIR;
      if (!fs.existsSync(pdfDir)) fs.mkdirSync(pdfDir, { recursive: true });
      const pdfPath = path.join(pdfDir, `receipt-${result.insertId}.pdf`);
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
        pdf_url: `/${process.env.PDFS_DIR}/receipt-${result.insertId}.pdf`
      });
    });

  } catch (err) {
    console.error("❌ Server error:", err.message);
    res.status(500).json({ success: false, message: "Server error" });
  }
});

// ---------------- Start Server ----------------
app.listen(PORT, () => {
  console.log(`🚀 Server running at http://localhost:${PORT}`);
});
