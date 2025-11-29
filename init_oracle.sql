/*
  init_oracle.sql
  Oracle 21c-compatible, idempotent schema + seed data for UCLMVenueReservation

  Notes:
  - Uses IDENTITY columns (Oracle 12c+) for auto-increment behavior.
  - The script is written to be re-runnable: object creation is wrapped in PL/SQL
    blocks which ignore "object already exists" errors.
  - The application originally created a `User` table. "USER" can be a special
    identifier in Oracle. This script creates a table named USER (unquoted).
    If your environment disallows that name, rename the table and adjust the
    application SQL accordingly.
  - The admin user password_hash is left NULL here because the original PHP
    creates a bcrypt hash at runtime. After running this script, create or
    update the admin password through the application or by calling PHP's
    password_hash() and updating the table.

  To run in SQL*Plus or SQL Developer:
    -- connect user/schema where you want the tables to be created
    @init_oracle.sql

*/
SET DEFINE OFF;

-- 1) Create tables (idempotent)
BEGIN
  EXECUTE IMMEDIATE 'CREATE TABLE Building (
    Building_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    Building_name VARCHAR2(255) NOT NULL UNIQUE
  )';
EXCEPTION
  WHEN OTHERS THEN
    IF SQLCODE != -955 THEN RAISE; END IF; -- ORA-00955: name is already used by an existing object
END;
/

BEGIN
  EXECUTE IMMEDIATE 'CREATE TABLE Department (
    department_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    department_name VARCHAR2(255) NOT NULL UNIQUE
  )';
EXCEPTION
  WHEN OTHERS THEN
    IF SQLCODE != -955 THEN RAISE; END IF;
END;
/

BEGIN
  EXECUTE IMMEDIATE 'CREATE TABLE "User" (
    user_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_name VARCHAR2(255) NOT NULL,
    password_hash VARCHAR2(255),
    role VARCHAR2(50) NOT NULL,
    department_id NUMBER,
    CONSTRAINT fk_user_department FOREIGN KEY (department_id) REFERENCES Department(department_id)
  )';
EXCEPTION
  WHEN OTHERS THEN
    IF SQLCODE != -955 THEN RAISE; END IF;
END;
/

BEGIN
  EXECUTE IMMEDIATE 'CREATE TABLE Venue (
    venue_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    venue_name VARCHAR2(255) NOT NULL UNIQUE,
    floor_number NUMBER NOT NULL,
    image_path VARCHAR2(4000),
    Building_id NUMBER,
    CONSTRAINT fk_venue_building FOREIGN KEY (Building_id) REFERENCES Building(Building_id)
  )';
EXCEPTION
  WHEN OTHERS THEN
    IF SQLCODE != -955 THEN RAISE; END IF;
END;
/

BEGIN
  EXECUTE IMMEDIATE 'CREATE TABLE Reservation_Status (
    status_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    status_name VARCHAR2(100) NOT NULL UNIQUE
  )';
EXCEPTION
  WHEN OTHERS THEN
    IF SQLCODE != -955 THEN RAISE; END IF;
END;
/

BEGIN
  EXECUTE IMMEDIATE 'CREATE TABLE Reservation (
    reservation_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    reserved_by VARCHAR2(255) NOT NULL,
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP NOT NULL,
    venue_id NUMBER,
    user_id NUMBER,
    status_id NUMBER,
    created_at TIMESTAMP DEFAULT SYSTIMESTAMP,
    CONSTRAINT fk_reservation_venue FOREIGN KEY (venue_id) REFERENCES Venue(venue_id),
    CONSTRAINT fk_reservation_user FOREIGN KEY (user_id) REFERENCES "User"(user_id),
    CONSTRAINT fk_reservation_status FOREIGN KEY (status_id) REFERENCES Reservation_Status(status_id)
  )';
EXCEPTION
  WHEN OTHERS THEN
    IF SQLCODE != -955 THEN RAISE; END IF;
END;
/

BEGIN
  EXECUTE IMMEDIATE 'CREATE TABLE Equipment (
    equipment_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    equipment_name VARCHAR2(255) NOT NULL UNIQUE,
    quantity NUMBER NOT NULL,
    description VARCHAR2(4000),
    venue_id NUMBER,
    CONSTRAINT fk_equipment_venue FOREIGN KEY (venue_id) REFERENCES Venue(venue_id)
  )';
EXCEPTION
  WHEN OTHERS THEN
    IF SQLCODE != -955 THEN RAISE; END IF;
END;
/

BEGIN
  EXECUTE IMMEDIATE 'CREATE TABLE Notification (
    notification_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id NUMBER,
    message VARCHAR2(4000) NOT NULL,
    created_at TIMESTAMP NOT NULL,
    is_read CHAR(1) DEFAULT ''N'',
    CONSTRAINT fk_notification_user FOREIGN KEY (user_id) REFERENCES "User"(user_id)
  )';
EXCEPTION
  WHEN OTHERS THEN
    IF SQLCODE != -955 THEN RAISE; END IF;
END;
/

-- 2) Seed Reservation_Status
MERGE INTO Reservation_Status rs
USING (SELECT 'Pending' AS status_name FROM DUAL) src
ON (rs.status_name = src.status_name)
WHEN NOT MATCHED THEN INSERT (status_id, status_name) VALUES (DEFAULT, src.status_name);

MERGE INTO Reservation_Status rs
USING (SELECT 'Approved' AS status_name FROM DUAL) src
ON (rs.status_name = src.status_name)
WHEN NOT MATCHED THEN INSERT (status_id, status_name) VALUES (DEFAULT, src.status_name);

MERGE INTO Reservation_Status rs
USING (SELECT 'Denied' AS status_name FROM DUAL) src
ON (rs.status_name = src.status_name)
WHEN NOT MATCHED THEN INSERT (status_id, status_name) VALUES (DEFAULT, src.status_name);

MERGE INTO Reservation_Status rs
USING (SELECT 'Cancelled' AS status_name FROM DUAL) src
ON (rs.status_name = src.status_name)
WHEN NOT MATCHED THEN INSERT (status_id, status_name) VALUES (DEFAULT, src.status_name);

-- 3) Seed Department list
DECLARE
  departments SYS.ODCIVARCHAR2LIST := SYS.ODCIVARCHAR2LIST(
    'AAC','ACCOUNTING','CAD','CARES','CASHIER','CCS','CDRC','CHTM',
    'CLINIC','CRIMINOLOGY','CTE','EDP','ERS','GUIDANCE','HR','IQA',
    'LIBRARY','MARE','MDO','MT','NSA','NURSING','OTO','PCO',
    'REGISTRAR','SAO','SCHOLARSHIP','TETAC','URO'
  );
BEGIN
  FOR i IN 1 .. departments.COUNT LOOP
    MERGE INTO Department d
    USING (SELECT departments(i) AS department_name FROM DUAL) src
    ON (d.department_name = src.department_name)
    WHEN NOT MATCHED THEN INSERT (department_id, department_name) VALUES (DEFAULT, src.department_name);
  END LOOP;
END;
/

-- 4) Seed Buildings
MERGE INTO Building b
USING (SELECT 'Main Building' AS Building_name FROM DUAL) src
ON (b.Building_name = src.Building_name)
WHEN NOT MATCHED THEN INSERT (Building_id, Building_name) VALUES (DEFAULT, src.Building_name);

MERGE INTO Building b
USING (SELECT 'Maritime Building' AS Building_name FROM DUAL) src
ON (b.Building_name = src.Building_name)
WHEN NOT MATCHED THEN INSERT (Building_id, Building_name) VALUES (DEFAULT, src.Building_name);

MERGE INTO Building b
USING (SELECT 'Basic Education Building' AS Building_name FROM DUAL) src
ON (b.Building_name = src.Building_name)
WHEN NOT MATCHED THEN INSERT (Building_id, Building_name) VALUES (DEFAULT, src.Building_name);

-- 5) Seed Venues (assumes the Building rows above exist)
-- We'll look up building ids via subqueries
MERGE INTO Venue v
USING (SELECT 'Old AVR' AS venue_name, 1 AS floor_number,
              (SELECT Building_id FROM Building WHERE Building_name = 'Main Building' AND ROWNUM = 1) AS Building_id
       FROM DUAL) src
ON (v.venue_name = src.venue_name)
WHEN NOT MATCHED THEN INSERT (venue_id, venue_name, floor_number, Building_id) VALUES (DEFAULT, src.venue_name, src.floor_number, src.Building_id);

MERGE INTO Venue v
USING (SELECT 'Maritime New AVR' AS venue_name, 1 AS floor_number,
              (SELECT Building_id FROM Building WHERE Building_name = 'Maritime Building' AND ROWNUM = 1) AS Building_id
       FROM DUAL) src
ON (v.venue_name = src.venue_name)
WHEN NOT MATCHED THEN INSERT (venue_id, venue_name, floor_number, Building_id) VALUES (DEFAULT, src.venue_name, src.floor_number, src.Building_id);

MERGE INTO Venue v
USING (SELECT 'Basic Education Function Hall' AS venue_name, 1 AS floor_number,
              (SELECT Building_id FROM Building WHERE Building_name = 'Basic Education Building' AND ROWNUM = 1) AS Building_id
       FROM DUAL) src
ON (v.venue_name = src.venue_name)
WHEN NOT MATCHED THEN INSERT (venue_id, venue_name, floor_number, Building_id) VALUES (DEFAULT, src.venue_name, src.floor_number, src.Building_id);

MERGE INTO Venue v
USING (SELECT 'Basic Education Auditorium' AS venue_name, 1 AS floor_number,
              (SELECT Building_id FROM Building WHERE Building_name = 'Basic Education Building' AND ROWNUM = 1) AS Building_id
       FROM DUAL) src
ON (v.venue_name = src.venue_name)
WHEN NOT MATCHED THEN INSERT (venue_id, venue_name, floor_number, Building_id) VALUES (DEFAULT, src.venue_name, src.floor_number, src.Building_id);

-- 6) Default admin user placeholder
-- Note: original app generates a bcrypt hash at runtime. Update password_hash using
-- PHP's password_hash() output, or create the admin via the app's registration endpoint.
MERGE INTO "User" u
USING (SELECT 'admin' AS user_name, NULL AS password_hash, 'admin' AS role, 
              (SELECT department_id FROM Department WHERE department_name = 'AAC' AND ROWNUM = 1) AS department_id
       FROM DUAL) src
ON (u.user_name = src.user_name)
WHEN NOT MATCHED THEN INSERT (user_id, user_name, password_hash, role, department_id) VALUES (DEFAULT, src.user_name, src.password_hash, src.role, src.department_id);

COMMIT;

PROMPT "Oracle schema and seed data applied (or already present)."