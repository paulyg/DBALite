BEGIN TRANSACTION;
CREATE TABLE `Categories` (
CategoryID int NOT NULL,
CategoryName varchar(15) NOT NULL,
Description text,
Picture blob,
PRIMARY KEY (CategoryID));

CREATE TABLE `CustomerCustomerDemo` (
CustomerID varchar(5) NOT NULL,
CustomerTypeID varchar(10) NOT NULL,
PRIMARY KEY (CustomerID,CustomerTypeID));

CREATE TABLE `CustomerDemographics` (
CustomerTypeID varchar(10) NOT NULL,
CustomerDesc text,
PRIMARY KEY (CustomerTypeID));

CREATE TABLE `Customers` (
CustomerID varchar(5) NOT NULL,
CompanyName varchar(40) NOT NULL,
ContactName varchar(30),
ContactTitle varchar(30),
Address varchar(60),
City varchar(15),
Region varchar(15),
PostalCode varchar(10),
Country varchar(15),
Phone varchar(24),
Fax varchar(24),
PRIMARY KEY (CustomerID));

CREATE TABLE `Employees` (
EmployeeID int NOT NULL,
LastName varchar(20) NOT NULL,
FirstName varchar(10) NOT NULL,
Title varchar(30),
TitleOfCourtesy varchar(25),
BirthDate timestamp,
HireDate timestamp,
Address varchar(60),
City varchar(15),
Region varchar(15),
PostalCode varchar(10),
Country varchar(15),
HomePhone varchar(24),
Extension varchar(4),
Photo blob,
Notes text,
ReportsTo int,
PhotoPath varchar(255),
PRIMARY KEY (EmployeeID));

CREATE TABLE `EmployeeTerritories` (
EmployeeID int NOT NULL,
TerritoryID varchar(20) NOT NULL,
PRIMARY KEY (EmployeeID,TerritoryID));

CREATE TABLE `Order Details` (
OrderID int,
ProductID int,
UnitPrice float(26),
Quantity int,
Discount float(13),
PRIMARY KEY (OrderID,ProductID));

CREATE TABLE `Orders` (
OrderID int NOT NULL,
CustomerID varchar(5),
EmployeeID int,
OrderDate timestamp,
RequiredDate timestamp,
ShippedDate timestamp,
ShipVia int,
Freight float(26),
ShipName varchar(40),
ShipAddress varchar(60),
ShipCity varchar(15),
ShipRegion varchar(15),
ShipPostalCode varchar(10),
ShipCountry varchar(15),
PRIMARY KEY (OrderID));

CREATE TABLE `Products` (
ProductID int NOT NULL,
ProductName varchar(40) NOT NULL,
SupplierID int,
CategoryID int,
QuantityPerUnit varchar(20),
UnitPrice float(26),
UnitsInStock int,
UnitsOnOrder int,
ReorderLevel int,
Discontinued int NOT NULL,
PRIMARY KEY (ProductID));

CREATE TABLE `Region` (
RegionID int NOT NULL,
RegionDescription varchar(50) NOT NULL,
PRIMARY KEY (RegionID));

CREATE TABLE `Shippers` (
ShipperID int NOT NULL,
CompanyName varchar(40) NOT NULL,
Phone varchar(24),
PRIMARY KEY (ShipperID));

CREATE TABLE `Suppliers` (
SupplierID int NOT NULL,
CompanyName varchar(40) NOT NULL,
ContactName varchar(30),
ContactTitle varchar(30),
Address varchar(60),
City varchar(15),
Region varchar(15),
PostalCode varchar(10),
Country varchar(15),
Phone varchar(24),
Fax varchar(24),
HomePage text,
PRIMARY KEY (SupplierID));

CREATE TABLE `Territories` (
TerritoryID varchar(20) NOT NULL,
TerritoryDescription varchar(50) NOT NULL,
RegionID int NOT NULL,
PRIMARY KEY (TerritoryID));


CREATE VIEW "Alphabetical list of products" AS
SELECT Products.*, Categories.CategoryName
FROM Categories INNER JOIN Products ON Categories.CategoryID = Products.CategoryID
WHERE (((Products.Discontinued)=0));

CREATE VIEW "Current Product List" AS
SELECT Product_List.ProductID, Product_List.ProductName
FROM Products AS Product_List
WHERE (((Product_List.Discontinued)=0));

CREATE VIEW "Customer and Suppliers by City" AS
SELECT City, CompanyName, ContactName, 'Customers' AS Relationship 
FROM Customers
UNION SELECT City, CompanyName, ContactName, 'Suppliers'
FROM Suppliers;

CREATE VIEW "Order Details Extended" AS
SELECT "Order Details".OrderID as OrderID, "Order Details".ProductID as ProductID, Products.ProductName, 
"Order Details".UnitPrice as UnitPrice, "Order Details".Quantity as Quantity, "Order Details".Discount as Discount, 
("Order Details".UnitPrice*Quantity*(1-Discount)/100)*100 AS ExtendedPrice
FROM Products INNER JOIN "Order Details" ON Products.ProductID = "Order Details".ProductID
--ORDER BY "Order Details".OrderID;

CREATE VIEW "Order Subtotals" AS
SELECT "Order Details".OrderID as OrderID, Sum(("Order Details".UnitPrice*Quantity*(1-Discount)/100)*100) AS Subtotal
FROM "Order Details"
GROUP BY "Order Details".OrderID;

CREATE VIEW "Summary of Sales by Quarter" AS
SELECT Orders.ShippedDate, Orders.OrderID, "Order Subtotals".Subtotal
FROM Orders INNER JOIN "Order Subtotals" ON Orders.OrderID = "Order Subtotals".OrderID
WHERE Orders.ShippedDate IS NOT NULL
--ORDER BY Orders.ShippedDate;

CREATE VIEW "Summary of Sales by Year" AS
SELECT Orders.ShippedDate, Orders.OrderID, "Order Subtotals".Subtotal
FROM Orders INNER JOIN "Order Subtotals" ON Orders.OrderID = "Order Subtotals".OrderID
WHERE Orders.ShippedDate IS NOT NULL
--ORDER BY Orders.ShippedDate;

CREATE VIEW "Orders Qry" AS
SELECT Orders.OrderID, Orders.CustomerID, Orders.EmployeeID, Orders.OrderDate, Orders.RequiredDate, 
Orders.ShippedDate, Orders.ShipVia, Orders.Freight, Orders.ShipName, Orders.ShipAddress, Orders.ShipCity, 
Orders.ShipRegion, Orders.ShipPostalCode, Orders.ShipCountry, 
Customers.CompanyName, Customers.Address, Customers.City, Customers.Region, Customers.PostalCode, Customers.Country
FROM Customers INNER JOIN Orders ON Customers.CustomerID = Orders.CustomerID;

CREATE VIEW "Products Above Average Price" AS
SELECT Products.ProductName, Products.UnitPrice
FROM Products
WHERE Products.UnitPrice>(SELECT AVG(UnitPrice) From Products);

CREATE VIEW "Products by Category" AS
SELECT Categories.CategoryName, Products.ProductName, Products.QuantityPerUnit, Products.UnitsInStock, Products.Discontinued
FROM Categories INNER JOIN Products ON Categories.CategoryID = Products.CategoryID
WHERE Products.Discontinued <> 1;
COMMIT;
