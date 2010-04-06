CREATE TABLE `Products` (
ProductID INT NOT NULL,
ProductName VARCHAR(40) NOT NULL,
SupplierID INT,
CategoryID INT,
QuantityPerUnit VARCHAR(20),
UnitPrice FLOAT(26),
UnitsInStock INT,
ReorderLevel INT,
PRIMARY KEY (ProductID)
) ENGINE=MyISAM;

CREATE TABLE Cars (
id INT NOT NULL AUTO_INCREMENT,
make VARCHAR(50),
model VARCHAR(50),
trim VARCHAR (20),
numcyls INT,
enginesize FLOAT,
PRIMARY KEY (id)
) ENGINE=MyISAM;
