CREATE TABLE IF NOT EXISTS tracking
(
    phone_number TEXT NOT NULL,
    city TEXT NOT NULL,
    CONSTRAINT tracking_pk PRIMARY KEY (phone_number, city)
);
