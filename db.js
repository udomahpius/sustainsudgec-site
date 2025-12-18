require("dotenv").config();
const mysql = require("mysql2");

const useHostinger = process.env.DB_ENV === "HOSTINGER";

const dbConfig = useHostinger
  ? {
      host: process.env.DB_HOSTINGER_HOST,
      user: process.env.DB_HOSTINGER_USER,
      password: process.env.DB_HOSTINGER_PASSWORD,
      database: process.env.DB_HOSTINGER_NAME,
      port: process.env.DB_HOSTINGER_PORT || 3306,
      connectTimeout: 10000,
    }
  : {
      host: process.env.DB_LOCAL_HOST,
      user: process.env.DB_LOCAL_USER,
      password: process.env.DB_LOCAL_PASSWORD,
      database: process.env.DB_LOCAL_NAME,
      port: process.env.DB_LOCAL_PORT || 3306,
      connectTimeout: 10000,
    };

const db = mysql.createConnection(dbConfig);

db.connect(err => {
  if (err) {
    console.error("❌ MySQL connection failed!");
    console.error("Error code:", err.code);
    console.error("Error message:", err.message);
    console.error("Host:", dbConfig.host);
    console.error("User:", dbConfig.user);
    process.exit(1);
  } else {
    console.log(`✅ Connected to MySQL successfully! (${useHostinger ? "Hostinger" : "Local"})`);
  }
});

module.exports = db;
