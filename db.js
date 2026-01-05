const mysql = require('mysql2');

const db = mysql.createPool({
  host: 'srv1817.hstgr.io',
  user: 'SUDGEC2000',
  password: 'SUDGEc#2000@',
  database: 'SUDGEC2000'
});

module.exports = db;
