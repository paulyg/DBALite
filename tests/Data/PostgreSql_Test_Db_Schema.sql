CREATE TABLE "Products" (
"ProductID" INTEGER PRIMARY KEY,
"ProductName" VARCHAR(40) NOT NULL,
"SupplierID" INTEGER,
"CategoryID" INTEGER,
"QuantityPerUnit" VARCHAR(20),
"UnitPrice" NUMERIC(6,2),
"UnitsInStock" INTEGER,
"ReorderLevel" INTEGER
);

CREATE TABLE "Cars" (
id SERIAL PRIMARY KEY,
make VARCHAR(50),
model VARCHAR(50),
trim VARCHAR (20),
numcyls INTEGER,
enginesize NUMERIC(3,1)
);
