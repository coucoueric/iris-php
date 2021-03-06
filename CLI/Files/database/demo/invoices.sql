DROP TABLE IF EXISTS "invoices";
CREATE TABLE invoices(
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL , 
    InvoiceDate DATE,
    customer_id INTEGER,
    Amount NUMBER,
    FOREIGN KEY (customer_id) REFERENCES customers(id));
INSERT INTO "invoices" VALUES(1,'2012-01-05',1,NULL);
INSERT INTO "invoices" VALUES(2,'2012-01-05',2,NULL);
INSERT INTO "invoices" VALUES(3,'2012-01-05',3,NULL);
INSERT INTO "invoices" VALUES(4,'2012-02-13',1,NULL);
INSERT INTO "invoices" VALUES(5,'2012-02-21',1,NULL);
INSERT INTO "invoices" VALUES(6,'2012-03-05',3,NULL);
