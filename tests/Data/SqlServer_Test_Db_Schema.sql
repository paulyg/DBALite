CREATE TABLE "Products" (
ProductID INT PRIMARY KEY,
ProductName NVARCHAR(40) NOT NULL,
SupplierID INT,
CategoryID INT,
QuantityPerUnit VARCHAR(20),
UnitPrice NUMERIC(6,2),
UnitsInStock INT,
ReorderLevel INT,
);

CREATE TABLE "Cars" (
id SERIAL PRIMARY KEY,
make VARCHAR(50),
model VARCHAR(50),
trim VARCHAR (20),
numcyls INT,
enginesize NUMERIC(3,1)
);
