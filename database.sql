-- EthioStay Database Schema for XAMPP/MySQL
-- Run this in phpMyAdmin or MySQL command line

CREATE DATABASE IF NOT EXISTS ethiostay_db;
USE ethiostay_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255),
    phone VARCHAR(20),
    google_id VARCHAR(255) UNIQUE,
    profile_picture VARCHAR(255),
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Hotels table
CREATE TABLE IF NOT EXISTS hotels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    address VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    star_rating INT DEFAULT 3,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Rooms table
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    room_type VARCHAR(100) NOT NULL,
    price_per_night DECIMAL(10,2) NOT NULL,
    max_occupancy INT DEFAULT 2,
    room_size INT,
    bed_type VARCHAR(100),
    amenities TEXT,
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE
);

-- Services table
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    service_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE
);

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    hotel_id INT NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    total_room_price DECIMAL(10,2) NOT NULL,
    total_services_price DECIMAL(10,2) DEFAULT 0,
    grand_total DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE
);

-- Booking Services junction table
CREATE TABLE IF NOT EXISTS booking_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    service_id INT NOT NULL,
    quantity INT DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('telebirr', 'cbe_birr', 'bank_transfer') NOT NULL,
    payment_status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
    transaction_id VARCHAR(255),
    user_transaction_reference VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Room Availability table (for daily availability management)
CREATE TABLE IF NOT EXISTS room_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    available_date DATE NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_room_date (room_id, available_date),
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- Insert sample admin user (password: Admin@123)
INSERT INTO users (full_name, email, password, role) 
VALUES ('Admin User', 'admin@ethiostay.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample customer user (password: Customer@123)
INSERT INTO users (full_name, email, password, phone, role) 
VALUES ('Test Customer', 'customer@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+251911123456', 'customer');

-- Insert comprehensive hotels across all Ethiopian regions
INSERT INTO hotels (user_id, name, description, address, city, phone, email, star_rating) VALUES
-- Addis Ababa (Capital City)
(1, 'Sheraton Addis', 'Luxury hotel in the heart of Addis Ababa with world-class amenities and stunning views.', 'Taitu Street, P.O. Box 6003', 'Addis Ababa', '+251-11-517-0000', 'info@sheratonaddis.com', 5),
(1, 'Hilton Addis Ababa', 'Modern hotel featuring comfortable rooms, excellent dining, and business facilities.', 'Menelik II Avenue', 'Addis Ababa', '+251-11-551-0000', 'hilton.addis@hilton.com', 4),
(1, 'Jupiter International Hotel', 'Contemporary hotel with Ethiopian hospitality and modern conveniences.', 'Bole Road', 'Addis Ababa', '+251-11-663-0000', 'info@jupiterhotel.com', 4),
(1, 'Radisson Blu Addis Ababa', 'Premium hotel with modern design, excellent service, and meeting facilities.', 'Meskel Square', 'Addis Ababa', '+251-11-553-0000', 'info@radissonbluaddis.com', 5),
(1, 'Ethiopian Skylight Hotel', 'Modern hotel near Bole International Airport with business amenities.', 'Bole area, Airport Road', 'Addis Ababa', '+251-11-668-0000', 'info@skylighthotel.com', 4),

-- Tigray Region
(1, 'Gheralta Lodge', 'Historic lodge in Tigray region with access to ancient rock-hewn churches.', 'Mekelle', '+251-34-440-0000', 'info@ghertalalodge.com', 3),
(1, 'Axum Hotel', 'Historic hotel near the ancient obelisks and St. Mary of Zion Church.', 'Axum', '+251-34-755-0000', 'info@axumhotel.com', 3),
(1, 'Mekelle Hotel', 'Comfortable hotel in the heart of Mekelle city with modern amenities.', 'Mekelle', '+251-34-441-0000', 'info@mekellehotel.com', 3),
(1, 'Adwa Hotel', 'Historic hotel near the famous Battle of Adwa site.', 'Adwa', '+251-34-756-0000', 'info@adwahotel.com', 3),

-- Amhara Region
(1, 'Gondara Hills Hotel', 'Scenic hotel in Gondar near the famous castles and churches.', 'Gondar', '+251-58-111-0000', 'info@gondarahills.com', 3),
(1, 'Bahir Dar Resort', 'Lakefront resort near the Blue Nile Falls and monasteries.', 'Bahir Dar', '+251-58-220-0000', 'info@bahirdarresort.com', 4),
(1, 'Lalibela Lodge', 'Traditional lodge near the famous rock-hewn churches of Lalibela.', 'Lalibela', '+251-33-111-0000', 'info@lalibelalodge.com', 3),
(1, 'Debre Birhan Hotel', 'Mountain hotel with stunning views in the historic city of Debre Birhan.', 'Debre Birhan', '+251-33-112-0000', 'info@debrebirhanhotel.com', 3),

-- Oromia Region
(1, 'Kuriftu Resort & Spa', 'Beautiful resort overlooking Lake Langano with spa and recreational facilities.', 'Langano', '+251-11-555-0000', 'info@kuriftu.com', 4),
(1, 'Sabana Beach Resort', 'Beachfront resort on Lake Hawassa with stunning views and modern amenities.', 'Hawassa', '+251-46-220-0000', 'info@sabanaresort.com', 4),
(1, 'Bale Mountain Lodge', 'Eco-lodge in Bale Mountains National Park for nature lovers.', 'Bale Mountains', '+251-11-666-0000', 'info@balemountainlodge.com', 3),
(1, 'Jimma Resort Hotel', 'Modern resort in Jimma known for coffee plantations and natural beauty.', 'Jimma', '+251-47-111-0000', 'info@jimmaresort.com', 3),
(1, 'Adama Hilton', 'Modern hotel in the bustling city of Adama with business facilities.', 'Adama', '+251-22-111-0000', 'info@adamahilton.com', 4),
(1, 'Nekemte Hotel', 'Comfortable hotel in Nekemte with traditional Oromo hospitality.', 'Nekemte', '+251-57-111-0000', 'info@nekemtehotel.com', 3),
(1, 'Shashamane Resort', 'Peaceful resort in Shashamane with beautiful gardens.', 'Shashamane', '+251-46-221-0000', 'info@shashamaneresort.com', 3),

-- Somali Region
(1, 'Jijiga Hotel', 'Modern hotel in Jijiga with business amenities and traditional Somali hospitality.', 'Jijiga', '+251-25-113-0000', 'info@jijigahotel.com', 3),

-- Afar Region
(1, 'Danakil Resort', 'Adventure resort near the Danakil Depression with unique desert experiences.', 'Danakil', '+251-33-113-0000', 'info@danakilresort.com', 3),
(1, 'Awash Lodge', 'Eco-lodge near Awash National Park with wildlife viewing opportunities.', 'Awash', '+251-33-114-0000', 'info@awashlodge.com', 3),

-- Benishangul-Gumuz Region
(1, 'Assosa Hotel', 'Comfortable hotel in Assosa with modern amenities.', 'Assosa', '+251-57-112-0000', 'info@assosahotel.com', 3),

-- Gambela Region
(1, 'Gambela Resort', 'Unique resort in Gambela with access to national parks and diverse wildlife.', 'Gambela', '+251-47-112-0000', 'info@gambelaresort.com', 3),

-- SNNPR (Southern Nations, Nationalities, and Peoples' Region)
(1, 'Arbaminch Resort', 'Beautiful resort near Lakes Chamo and Abaya with crocodile viewing.', 'Arbaminch', '+251-11-777-0000', 'info@arbaminchresort.com', 3),
(1, 'Wolayita Sodo Hotel', 'Modern hotel in Wolayita Sodo with comfortable accommodations.', 'Wolayita Sodo', '+251-46-222-0000', 'info@wolayitasodohotel.com', 3),
(1, 'Hossana Hotel', 'Traditional hotel in Hossana with authentic Ethiopian hospitality.', 'Hossana', '+251-34-442-0000', 'info@hossanahotel.com', 3),
(1, 'Dilla Coffee Resort', 'Coffee-themed resort in Dilla with tours of coffee plantations.', 'Dilla', '+251-46-223-0000', 'info@dillacoffeeresort.com', 3),

-- Harari Region
(1, 'Harar Ras Hotel', 'Traditional hotel in the historic city of Harar near the ancient walls.', 'Harar', '+251-25-111-0000', 'info@hararrashotel.com', 3),

-- Dire Dawa City Administration
(1, 'Dire Dawa Hotel', 'Business hotel in the strategic city of Dire Dawa.', 'Dire Dawa', '+251-25-112-0000', 'info@diredawahotel.com', 3);

-- Insert rooms for Addis Ababa hotels
INSERT INTO rooms (hotel_id, room_type, price_per_night, max_occupancy, room_size, bed_type, amenities) VALUES
-- Sheraton Addis
(1, 'Deluxe King Room', 180.00, 2, 35, 'King Bed', 'WiFi, AC, TV, Mini Bar, Room Service'),
(1, 'Executive Suite', 280.00, 3, 55, 'King Bed + Sofa Bed', 'WiFi, AC, TV, Mini Bar, Room Service, Balcony, Bathtub'),
(1, 'Presidential Suite', 550.00, 4, 80, 'King Bed + Living Area', 'WiFi, AC, TV, Mini Bar, Room Service, Balcony, Bathtub, Butler Service'),
-- Hilton Addis Ababa
(2, 'Standard Room', 120.00, 2, 28, 'Queen Bed', 'WiFi, AC, TV'),
(2, 'Deluxe Room', 150.00, 2, 32, 'Queen Bed', 'WiFi, AC, TV, Mini Bar'),
(2, 'Family Room', 200.00, 4, 45, '2 Queen Beds', 'WiFi, AC, TV, Mini Bar, Sofa Bed'),
-- Jupiter International Hotel
(3, 'Standard Single', 90.00, 1, 22, 'Single Bed', 'WiFi, AC, TV'),
(3, 'Standard Double', 120.00, 2, 28, 'Double Bed', 'WiFi, AC, TV'),
(3, 'Deluxe Suite', 220.00, 3, 50, 'King Bed + Sofa', 'WiFi, AC, TV, Mini Bar, Balcony'),
-- Radisson Blu Addis Ababa
(4, 'Standard Room', 160.00, 2, 30, 'Queen Bed', 'WiFi, AC, TV, Mini Bar'),
(4, 'Business Class Room', 200.00, 2, 35, 'King Bed', 'WiFi, AC, TV, Mini Bar, Work Desk'),
(4, 'Executive Suite', 350.00, 3, 60, 'King Bed + Living Area', 'WiFi, AC, TV, Mini Bar, Balcony, Butler Service'),
-- Ethiopian Skylight Hotel
(5, 'Standard Room', 110.00, 2, 28, 'Queen Bed', 'WiFi, AC, TV'),
(5, 'Business Room', 140.00, 2, 32, 'King Bed', 'WiFi, AC, TV, Work Desk'),
(5, 'Suite', 200.00, 3, 50, 'King Bed + Sofa', 'WiFi, AC, TV, Mini Bar, Balcony');

-- Insert rooms for Tigray Region hotels
INSERT INTO rooms (hotel_id, room_type, price_per_night, max_occupancy, room_size, bed_type, amenities) VALUES
-- Gheralta Lodge
(6, 'Standard Room', 85.00, 2, 25, 'Double Bed', 'WiFi, Fan, TV'),
(6, 'Mountain View Room', 110.00, 2, 30, 'Queen Bed', 'WiFi, AC, TV, Balcony'),
-- Axum Hotel
(7, 'Obelisk View Room', 95.00, 2, 28, 'Queen Bed', 'WiFi, AC, TV'),
(7, 'Standard Room', 75.00, 2, 25, 'Double Bed', 'WiFi, Fan, TV'),
-- Mekelle Hotel
(8, 'Standard Room', 80.00, 2, 25, 'Double Bed', 'WiFi, AC, TV'),
(8, 'Deluxe Room', 110.00, 2, 30, 'Queen Bed', 'WiFi, AC, TV, Mini Bar'),
-- Adwa Hotel
(9, 'Standard Room', 70.00, 2, 25, 'Double Bed', 'WiFi, Fan, TV'),
(9, 'Historic Room', 90.00, 2, 28, 'Queen Bed', 'WiFi, AC, TV');

-- Insert rooms for Amhara Region hotels
INSERT INTO rooms (hotel_id, room_type, price_per_night, max_occupancy, room_size, bed_type, amenities) VALUES
-- Gondar Hills Hotel
(10, 'Castle View Room', 100.00, 2, 30, 'Queen Bed', 'WiFi, AC, TV, Balcony'),
(10, 'Garden Room', 85.00, 2, 25, 'Double Bed', 'WiFi, Fan, TV'),
-- Bahir Dar Resort
(11, 'Lake View Room', 120.00, 2, 32, 'King Bed', 'WiFi, AC, TV, Balcony'),
(11, 'Nile Falls View Room', 150.00, 2, 35, 'King Bed', 'WiFi, AC, TV, Balcony'),
-- Lalibela Lodge
(12, 'Traditional Room', 95.00, 2, 28, 'Double Bed', 'WiFi, Fan, TV'),
(12, 'Cave Style Room', 130.00, 2, 30, 'Queen Bed', 'WiFi, Heating, TV'),
-- Debre Birhan Hotel
(13, 'Mountain View Room', 90.00, 2, 28, 'Queen Bed', 'WiFi, AC, TV, Balcony'),
(13, 'Standard Room', 70.00, 2, 25, 'Double Bed', 'WiFi, Fan, TV');

-- Insert rooms for Oromia Region hotels
INSERT INTO rooms (hotel_id, room_type, price_per_night, max_occupancy, room_size, bed_type, amenities) VALUES
-- Kuriftu Resort & Spa
(14, 'Lake View Room', 120.00, 2, 30, 'Queen Bed', 'WiFi, AC, TV, Balcony'),
(14, 'Family Cottage', 250.00, 4, 60, '2 Queen Beds', 'WiFi, AC, TV, Kitchen, Balcony'),
-- Sabana Beach Resort
(15, 'Beach Front Room', 130.00, 2, 32, 'King Bed', 'WiFi, AC, TV, Balcony'),
(15, 'Garden View Room', 100.00, 2, 28, 'Queen Bed', 'WiFi, AC, TV'),
-- Bale Mountain Lodge
(16, 'Eco Cabin', 150.00, 2, 35, 'Queen Bed', 'WiFi, Heating, Fireplace, Balcony'),
(16, 'Family Tent', 200.00, 4, 50, '2 Queen Beds', 'WiFi, Heating, Shared Bathroom'),
-- Jimma Resort Hotel
(17, 'Coffee Plantation Room', 110.00, 2, 32, 'King Bed', 'WiFi, AC, TV, Balcony'),
(17, 'Garden View Room', 90.00, 2, 28, 'Queen Bed', 'WiFi, AC, TV'),
-- Adama Hilton
(18, 'Business Room', 100.00, 2, 30, 'Queen Bed', 'WiFi, AC, TV, Desk'),
(18, 'Standard Room', 80.00, 2, 25, 'Double Bed', 'WiFi, AC, TV'),
-- Nekemte Hotel
(19, 'Standard Room', 75.00, 2, 25, 'Double Bed', 'WiFi, Fan, TV'),
(19, 'Deluxe Room', 95.00, 2, 28, 'Queen Bed', 'WiFi, AC, TV'),
-- Shashamane Resort
(20, 'Garden Room', 85.00, 2, 28, 'Queen Bed', 'WiFi, AC, TV'),
(20, 'Family Suite', 120.00, 4, 45, '2 Queen Beds', 'WiFi, AC, TV, Kitchen');

-- Insert rooms for Somali Region hotels
INSERT INTO rooms (hotel_id, room_type, price_per_night, max_occupancy, room_size, bed_type, amenities) VALUES
-- Jijiga Hotel
(21, 'Standard Room', 70.00, 2, 25, 'Double Bed', 'WiFi, AC, TV'),
(21, 'Business Room', 90.00, 2, 30, 'Queen Bed', 'WiFi, AC, TV, Work Desk');

-- Insert rooms for Afar Region hotels
INSERT INTO rooms (hotel_id, room_type, price_per_night, max_occupancy, room_size, bed_type, amenities) VALUES
-- Danakil Resort
(22, 'Adventure Tent', 150.00, 2, 30, '2 Single Beds', 'WiFi, Cooling System, Shared Bath'),
(22, 'Dome Room', 200.00, 2, 35, 'King Bed', 'WiFi, AC, TV, Private Bath'),
-- Awash Lodge
(23, 'Safari Tent', 120.00, 2, 28, '2 Single Beds', 'WiFi, Fan, Shared Bath'),
(23, 'Standard Room', 90.00, 2, 25, 'Double Bed', 'WiFi, AC, TV');

-- Insert rooms for Benishangul-Gumuz Region hotels
INSERT INTO rooms (hotel_id, room_type, price_per_night, max_occupancy, room_size, bed_type, amenities) VALUES
-- Assosa Hotel
(24, 'Standard Room', 70.00, 2, 25, 'Double Bed', 'WiFi, AC, TV'),
(24, 'Deluxe Room', 90.00, 2, 28, 'Queen Bed', 'WiFi, AC, TV, Mini Bar');

-- Insert rooms for Gambela Region hotels
INSERT INTO rooms (hotel_id, room_type, price_per_night, max_occupancy, room_size, bed_type, amenities) VALUES
-- Gambela Resort
(25, 'Safari Room', 100.00, 2, 30, 'Queen Bed', 'WiFi, AC, TV, Balcony'),
(25, 'Standard Room', 80.00, 2, 25, 'Double Bed', 'WiFi, Fan, TV');

-- Insert rooms for SNNPR hotels
INSERT INTO rooms (hotel_id, room_type, price_per_night, max_occupancy, room_size, bed_type, amenities) VALUES
-- Arbaminch Resort
(26, 'Crocodile View Room', 100.00, 2, 28, 'Queen Bed', 'WiFi, AC, TV, Balcony'),
(26, 'Lake View Room', 90.00, 2, 25, 'Double Bed', 'WiFi, AC, TV'),
-- Wolayita Sodo Hotel
(27, 'Standard Room', 75.00, 2, 25, 'Double Bed', 'WiFi, AC, TV'),
(27, 'Deluxe Room', 95.00, 2, 28, 'Queen Bed', 'WiFi, AC, TV, Mini Bar'),
-- Hossana Hotel
(28, 'Standard Room', 70.00, 2, 25, 'Double Bed', 'WiFi, Fan, TV'),
(28, 'Traditional Room', 85.00, 2, 28, 'Queen Bed', 'WiFi, AC, TV'),
-- Dilla Coffee Resort
(29, 'Coffee Garden Room', 90.00, 2, 28, 'Queen Bed', 'WiFi, AC, TV, Balcony'),
(29, 'Standard Room', 75.00, 2, 25, 'Double Bed', 'WiFi, AC, TV');

-- Insert rooms for Harari Region hotels
INSERT INTO rooms (hotel_id, room_type, price_per_night, max_occupancy, room_size, bed_type, amenities) VALUES
-- Harar Ras Hotel
(30, 'Traditional Room', 90.00, 2, 28, 'Double Bed', 'WiFi, Fan, TV'),
(30, 'Courtyard Room', 110.00, 2, 32, 'Queen Bed', 'WiFi, AC, TV, Balcony');

-- Insert rooms for Dire Dawa hotels
INSERT INTO rooms (hotel_id, room_type, price_per_night, max_occupancy, room_size, bed_type, amenities) VALUES
-- Dire Dawa Hotel
(31, 'Business Room', 100.00, 2, 30, 'Queen Bed', 'WiFi, AC, TV, Desk'),
(31, 'Standard Room', 80.00, 2, 25, 'Double Bed', 'WiFi, AC, TV');

-- Insert services for Addis Ababa hotels
INSERT INTO services (hotel_id, service_name, description, price) VALUES
-- Sheraton Addis
(1, 'Airport Transfer', 'Round-trip airport transfer service', 50.00),
(1, 'Spa Package', 'Full day spa access with massage', 80.00),
(1, 'Breakfast Buffet', 'Daily breakfast buffet per person', 25.00),
(1, 'Laundry Service', 'Full laundry and dry cleaning', 30.00),
(1, 'Business Center', 'Meeting room and business facilities', 40.00),
-- Hilton Addis Ababa
(2, 'Airport Pickup', 'One-way airport pickup', 30.00),
(2, 'Gym Access', 'Full gym and fitness center access', 20.00),
(2, 'Breakfast', 'Continental breakfast per person', 20.00),
(2, 'Pool Access', 'Swimming pool access', 15.00),
-- Jupiter International Hotel
(3, 'Airport Transfer', 'Airport transfer service', 40.00),
(3, 'Breakfast', 'Ethiopian breakfast per person', 15.00),
(3, 'City Tour', 'Guided city tour (half day)', 60.00),
-- Radisson Blu Addis Ababa
(4, 'Premium Breakfast', 'Gourmet breakfast buffet per person', 30.00),
(4, 'Fitness Center', 'State-of-the-art gym access', 25.00),
(4, 'Concierge Service', 'Personal concierge assistance', 50.00),
-- Ethiopian Skylight Hotel
(5, 'Airport Shuttle', 'Regular airport shuttle service', 25.00),
(5, 'Meeting Room', 'Business meeting facilities', 45.00),
(5, 'Breakfast', 'International breakfast per person', 20.00);

-- Insert services for Tigray Region hotels
INSERT INTO services (hotel_id, service_name, description, price) VALUES
-- Gheralta Lodge
(6, 'Historical Tour', 'Guided tour to rock-hewn churches', 80.00),
(6, 'Cultural Experience', 'Traditional Tigray cultural program', 45.00),
(6, 'Trekking Guide', 'Mountain trekking with local guide', 70.00),
-- Axum Hotel
(7, 'Ancient Sites Tour', 'Tour of Axum archaeological sites', 50.00),
(7, 'Church Visit', 'Visit to St. Mary of Zion Church', 20.00),
(7, 'Local Guide', 'Professional guide for historical sites', 40.00),
-- Mekelle Hotel
(8, 'City Tour', 'Mekelle city highlights tour', 35.00),
(8, 'Traditional Meal', 'Authentic Tigrayan cuisine dinner', 25.00),
-- Adwa Hotel
(9, 'Battlefield Tour', 'Historic Battle of Adwa site visit', 30.00),
(9, 'Cultural Show', 'Traditional Tigray cultural performance', 35.00);

-- Insert services for Amhara Region hotels
INSERT INTO services (hotel_id, service_name, description, price) VALUES
-- Gondar Hills Hotel
(10, 'Castle Tour', 'Guided tour of Gondar castles', 40.00),
(10, 'Simien Mountains Trek', 'Day trip to Simien Mountains', 120.00),
(10, 'Historical Walk', 'Walking tour of Gondar historic sites', 25.00),
-- Bahir Dar Resort
(11, 'Blue Nile Falls Tour', 'Guided tour to Tis Abay Falls', 60.00),
(11, 'Monastery Boat Tour', 'Boat tour to Lake Tana monasteries', 55.00),
(11, 'Lake Cruise', 'Sunset cruise on Lake Tana', 45.00),
-- Lalibela Lodge
(12, 'Church Tour', 'Comprehensive rock-hewn churches tour', 70.00),
(12, 'Traditional Ceremony', 'Ethiopian Orthodox ceremony experience', 30.00),
(12, 'Mountain Hike', 'Scenic mountain hiking with guide', 50.00),
-- Debre Birhan Hotel
(13, 'Mountain View Tour', 'Scenic mountain and landscape tour', 40.00),
(13, 'Traditional Coffee', 'Ethiopian coffee ceremony', 20.00);

-- Insert services for Oromia Region hotels
INSERT INTO services (hotel_id, service_name, description, price) VALUES
-- Kuriftu Resort & Spa
(14, 'Lake Activities', 'Boat rental and water sports', 60.00),
(14, 'Spa Treatment', 'Full body massage', 70.00),
(14, 'Beach Party', 'Private beach party organization', 150.00),
-- Sabana Beach Resort
(15, 'Beach Activities', 'Beach volleyball and water sports', 40.00),
(15, 'Fishing Tour', 'Guided fishing on Lake Hawassa', 50.00),
(15, 'Sunset Cruise', 'Evening boat cruise with dinner', 80.00),
-- Bale Mountain Lodge
(16, 'Wildlife Safari', 'Guided wildlife viewing tour', 100.00),
(16, 'Bird Watching', 'Bird watching expedition with guide', 60.00),
(16, 'Mountain Trekking', 'Bale Mountains trekking adventure', 120.00),
-- Jimma Resort Hotel
(17, 'Coffee Plantation Tour', 'Tour of coffee farms and processing', 45.00),
(17, 'Nature Walk', 'Guided nature walk in Jimma forests', 30.00),
(17, 'Coffee Tasting', 'Professional coffee tasting session', 25.00),
-- Adama Hilton
(18, 'Business Facilities', 'Conference room and business services', 60.00),
(18, 'City Tour', 'Adama city tour and shopping', 35.00),
-- Nekemte Hotel
(19, 'Cultural Experience', 'Traditional Oromo cultural program', 40.00),
(19, 'Nature Tour', 'Surrounding area nature exploration', 35.00),
-- Shashamane Resort
(20, 'Garden Tour', 'Botanical garden tour', 25.00),
(20, 'Wellness Package', 'Spa and wellness treatments', 55.00);

-- Insert services for Somali Region hotels
INSERT INTO services (hotel_id, service_name, description, price) VALUES
-- Jijiga Hotel
(21, 'Somali Cultural Experience', 'Traditional Somali cultural program', 45.00),
(21, 'Camel Trek', 'Desert camel trekking experience', 80.00),
(21, 'Traditional Meal', 'Authentic Somali cuisine dinner', 30.00);

-- Insert services for Afar Region hotels
INSERT INTO services (hotel_id, service_name, description, price) VALUES
-- Danakil Resort
(22, 'Danakil Expedition', 'Guided Danakil Depression tour', 200.00),
(22, 'Volcano Visit', 'Erta Ale volcano expedition', 250.00),
(22, 'Salt Lake Tour', 'Salt flats and sulfur lake visit', 150.00),
-- Awash Lodge
(23, 'Safari Tour', 'Awash National Park wildlife safari', 80.00),
(23, 'Waterfall Visit', 'Awash Falls guided visit', 40.00),
(23, 'Camping Experience', 'Wilderness camping adventure', 100.00);

-- Insert services for Benishangul-Gumuz Region hotels
INSERT INTO services (hotel_id, service_name, description, price) VALUES
-- Assosa Hotel
(24, 'Nature Tour', 'Benishangul region nature exploration', 50.00),
(24, 'Cultural Experience', 'Local indigenous cultural program', 40.00);

-- Insert services for Gambela Region hotels
INSERT INTO services (hotel_id, service_name, description, price) VALUES
-- Gambela Resort
(25, 'Wildlife Safari', 'Gambela National Park safari', 120.00),
(25, 'River Adventure', 'Baro River boat expedition', 80.00),
(25, 'Cultural Tour', 'Anuak and Nuer cultural experience', 50.00);

-- Insert services for SNNPR hotels
INSERT INTO services (hotel_id, service_name, description, price) VALUES
-- Arbaminch Resort
(26, 'Crocodile Farm Visit', 'Visit to Arba Minch crocodile farm', 40.00),
(26, 'Nechsar National Park', 'Safari in Nechsar National Park', 80.00),
(26, 'Lake Chamo Tour', 'Boat tour on Lake Chamo', 55.00),
-- Wolayita Sodo Hotel
(27, 'Cultural Tour', 'Wolayita cultural experience', 35.00),
(27, 'Agricultural Tour', 'Local farming and agriculture visit', 30.00),
-- Hossana Hotel
(28, 'Historical Tour', 'Hadiya Zone historical sites', 25.00),
(28, 'Traditional Ceremony', 'Local cultural ceremony participation', 30.00),
-- Dilla Coffee Resort
(29, 'Coffee Plantation Tour', 'Sidama coffee farm tour', 40.00),
(29, 'Coffee Ceremony', 'Traditional Sidama coffee ceremony', 25.00),
(29, 'Nature Walk', 'Coffee forest nature walk', 35.00);

-- Insert services for Harari Region hotels
INSERT INTO services (hotel_id, service_name, description, price) VALUES
-- Harar Ras Hotel
(30, 'City Heritage Tour', 'Tour of Harar walls and historic sites', 35.00),
(30, 'Coffee Ceremony', 'Traditional Ethiopian coffee ceremony', 25.00),
(30, 'Hyena Feeding', 'Evening hyena feeding experience', 30.00);

-- Insert services for Dire Dawa hotels
INSERT INTO services (hotel_id, service_name, description, price) VALUES
-- Dire Dawa Hotel
(31, 'Industrial Tour', 'Tour of Dire Dawa industrial sites', 35.00),
(31, 'Train Station Visit', 'Historic Ethio-Djibouti railway station', 15.00),
(31, 'City Tour', 'Dire Dawa city highlights tour', 30.00);
