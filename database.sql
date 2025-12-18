-- AgriShop Database Dump
-- Generated: 2025-12-15 16:41:56

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";



CREATE TABLE `advisory_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `title_rw` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `content` text NOT NULL,
  `content_rw` text NOT NULL,
  `date_published` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `advisory_posts` (`id`, `title`, `title_rw`, `type`, `content`, `content_rw`, `date_published`) VALUES ('1', 'Urgent: Fighting Fall Armyworm', 'Itangazo: Kurwanya Nkongwa y_Imyaka', 'Alert', 'Fall Armyworm has been reported in Eastern Province (Nyagatare). \n\nAction Plan:\n1. Inspect maize whorls early morning.\n2. If you see sawdust-like frass, mechanical control is best for small farms.\n3. Use Neem oil or Pyrethrum for organic control.\n4. Dig trenches around fields to stop migration.', 'Nkongwa yagaragaye mu Ntara y_Iburasirazuba (Nyagatare). \n\nIcyo gukora:\n1. Suzuma ibigori buri munsi mu gitondo.\n2. Niba ubonye ifu isa n_iy_urubaho, yirwanyishe intoki niba ubutaka ari buto.\n3. Koresha amavuta ya Neem cyangwa Pyrethrum.\n4. Cukura imiringoti uzengurutse umurima guhagarika ko yimuka.', '2025-12-10 12:08:27');
INSERT INTO `advisory_posts` (`id`, `title`, `title_rw`, `type`, `content`, `content_rw`, `date_published`) VALUES ('2', 'Season A Planting Guide: Maize & Beans', 'Igihembwe A: Itegurwa ry_Ibigori n_Ibishyimbo', 'Guide', 'Season A (Sept-Jan) is crucial for food security. \n\nKey Steps:\n1. Land Preparation: Finish by mid-August.\n2. Seed Selection: Use hybrid seeds (e.g., H628) for higher yield.\n3. Fertilizer: Apply DAP at planting and Urea 4 weeks later.\n4. Intercropping: Plant beans 2 weeks after maize to reduce competition.', 'Igihembwe A (Nzeri-Mutarama) ni ingenzi. \n\nIntambwe:\n1. Gutunganya ubutaka: Birangire hagati muri Kanama.\n2. Imbuto: Koresha imbuto z_indobanure (urugero: H628).\n3. Ifumbire: Shyiramo DAP utera, na Urea nyuma y_ibyumweru 4.\n4. Kuvangura: Tera ibishyimbo nyuma y_ibyumweru 2 uteye ibigori.', '2025-12-05 12:08:27');
INSERT INTO `advisory_posts` (`id`, `title`, `title_rw`, `type`, `content`, `content_rw`, `date_published`) VALUES ('3', 'Coffee Quality: Washing & Drying', 'Ubwiza bw_Ikawa: Kwoza no Kwanika', 'Quality', 'To get Grade A prices, strict hygiene is needed.\n\n1. Floating: Use water to remove light cherries (floaters).\n2. Fermentation: 12-24 hours depending on weather.\n3. Drying: Always use raised beds. Turn coffee every 30 minutes to ensure even drying to 12% moisture.', 'Kugira ngo ubone ibiciro bya Grade A:\n\n1. Kurobanura: Koresha amazi ukuremo ibitumbwe bitemba hejuru.\n2. Gutara: Amasaha 12-24 bitewe n_ikirere.\n3. Kwanika: Koresha ibitala byabugenewe. Hindura ikawa buri minota 30 kugeza yumye.', '2025-11-28 12:08:27');
INSERT INTO `advisory_posts` (`id`, `title`, `title_rw`, `type`, `content`, `content_rw`, `date_published`) VALUES ('4', 'Market Alert: Hass Avocado Demand', 'Isoko: Gakenewe Avoka za Hass', 'Market', 'European demand for Hass Avocados has increased by 20%.\n\nRequirement:\n- Must be Global G.A.P certified.\n- No chemical residue.\n- Size 12-14 preferred.\nContact AgriShop logistics for export aggregation centers near Huye.', 'Isoko ry_i Burayi rya Avoka za Hass ryazamutseho 20%.\n\nIbisabwa:\n- Kuba ufite icyemezo cya Global G.A.P.\n- Nta miti mibi.\n- Ingano 12-14 niyo ikunzwe.\nHamagara AgriShop logistics bakurangire ahoikusanyirizo riri hafi ya Huye.', '2025-11-21 12:08:27');
INSERT INTO `advisory_posts` (`id`, `title`, `title_rw`, `type`, `content`, `content_rw`, `date_published`) VALUES ('5', 'Post-Harvest Storage: PICS Bags', 'Guhunika: Imifuka ya PICS', 'Guide', 'Stop losing 30% of your beans to weevils.\n\nHow to use PICS bags:\n1. Dry grain completely.\n2. Fill the inner plastic bag and tie shut.\n3. Fill the second plastic bag and tie.\n4. Close the outer woven sack.\nNO CHEMICALS NEEDED.', 'Hagarika gutakaza 30% by_umusaruro kubera udukoko.\n\nUko bakoresha PICS:\n1. Anika imyaka yumye neza.\n2. Shyira mu isashi ya mbere ufunge.\n3. Shyira mu ya kabiri ufunge.\n4. Funga umufuka w_inyuma.\nNTA MUTI UKENEWE.', '2025-11-12 12:08:27');


CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` decimal(10,2) DEFAULT 1.00,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES ('1', '1', '2', '1.00', '400.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES ('2', '1', '3', '1.00', '250.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES ('3', '2', '1', '1.00', '4500.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES ('4', '2', '2', '1.00', '400.00');


CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `buyer_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_phone` varchar(20) DEFAULT NULL,
  `delivery_address` text DEFAULT NULL,
  `delivery_instructions` text DEFAULT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `buyer_id` (`buyer_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `orders` (`id`, `buyer_id`, `total_amount`, `delivery_phone`, `delivery_address`, `delivery_instructions`, `status`, `created_at`) VALUES ('1', '3', '650.00', '0785903160', 'musanze ', 'make sure they are good', 'pending', '2025-12-12 12:33:27');
INSERT INTO `orders` (`id`, `buyer_id`, `total_amount`, `delivery_phone`, `delivery_address`, `delivery_instructions`, `status`, `created_at`) VALUES ('2', '3', '4900.00', '0785903160', 'koj', 'hjlk', 'pending', '2025-12-12 13:02:02');


CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `name_rw` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `category` varchar(50) NOT NULL,
  `location` varchar(100) NOT NULL,
  `image_type` varchar(50) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_certified` tinyint(1) DEFAULT 0,
  `is_organic` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `products` (`id`, `user_id`, `name`, `name_rw`, `price`, `unit`, `category`, `location`, `image_type`, `image_path`, `is_certified`, `is_organic`, `created_at`) VALUES ('1', '4', 'Premium Arabica Coffee', 'Ikawa y_Arabica', '4500.00', 'kg', 'Export', 'Huye District', 'coffee', 'uploads/products/prod_1765805334_6354.jpg', '1', '1', '2025-12-12 12:08:27');
INSERT INTO `products` (`id`, `user_id`, `name`, `name_rw`, `price`, `unit`, `category`, `location`, `image_type`, `image_path`, `is_certified`, `is_organic`, `created_at`) VALUES ('2', '1', 'Irish Potatoes (Kinigi)', 'Ibirayi bya Kinigi', '400.00', 'kg', 'Produce', 'Musanze', 'potato', 'uploads/products/prod_1765752892_6319.jpg', '1', '0', '2025-12-12 12:08:27');
INSERT INTO `products` (`id`, `user_id`, `name`, `name_rw`, `price`, `unit`, `category`, `location`, `image_type`, `image_path`, `is_certified`, `is_organic`, `created_at`) VALUES ('3', '1', 'Organic Bananas', 'Ibitoki', '250.00', 'bunch', 'Produce', 'Rwamagana', 'banana', 'uploads/products/prod_1765752925_7841.jpg', '0', '1', '2025-12-12 12:08:27');
INSERT INTO `products` (`id`, `user_id`, `name`, `name_rw`, `price`, `unit`, `category`, `location`, `image_type`, `image_path`, `is_certified`, `is_organic`, `created_at`) VALUES ('4', '1', 'NPK Fertilizer', 'Ifumbire NPK', '1200.00', 'kg', 'Inputs', 'Kigali Warehouse', 'fertilizer', 'uploads/products/prod_1765753095_6354.png', '1', '0', '2025-12-12 12:08:27');
INSERT INTO `products` (`id`, `user_id`, `name`, `name_rw`, `price`, `unit`, `category`, `location`, `image_type`, `image_path`, `is_certified`, `is_organic`, `created_at`) VALUES ('5', '1', 'Maize Seeds (Hybrid)', 'Imbuto z_Ibigori', '800.00', 'kg', 'Seeds', 'Nyagatare', 'maize', 'uploads/products/prod_1765752904_7791.jpg', '1', '0', '2025-12-12 12:08:27');
INSERT INTO `products` (`id`, `user_id`, `name`, `name_rw`, `price`, `unit`, `category`, `location`, `image_type`, `image_path`, `is_certified`, `is_organic`, `created_at`) VALUES ('6', '1', 'Avocados', 'Avoka', '150.00', 'pc', 'Produce', 'Huye', 'avocado', 'uploads/products/prod_1765752821_9453.jpg', '0', '1', '2025-12-12 12:08:27');
INSERT INTO `products` (`id`, `user_id`, `name`, `name_rw`, `price`, `unit`, `category`, `location`, `image_type`, `image_path`, `is_certified`, `is_organic`, `created_at`) VALUES ('7', '1', 'Banana', 'Banana', '200.00', 'kg', 'Produce', 'Nyagatare', 'produce', 'uploads/products/prod_1765756975_6288.jpg', '0', '1', '2025-12-12 13:41:38');
INSERT INTO `products` (`id`, `user_id`, `name`, `name_rw`, `price`, `unit`, `category`, `location`, `image_type`, `image_path`, `is_certified`, `is_organic`, `created_at`) VALUES ('8', '4', 'Sweet potato', 'Irish potato', '430.00', 'kg', 'Produce', 'Kigali', 'produce', 'uploads/products/prod_1765805949_9387.jpg', '1', '0', '2025-12-15 13:37:37');
INSERT INTO `products` (`id`, `user_id`, `name`, `name_rw`, `price`, `unit`, `category`, `location`, `image_type`, `image_path`, `is_certified`, `is_organic`, `created_at`) VALUES ('9', '1', 'Carrots', 'Carrots', '300.00', 'kg', 'Produce', 'Bugesera', 'produce', 'uploads/products/prod_1765807452_8964.jpg', '1', '0', '2025-12-15 13:47:23');
INSERT INTO `products` (`id`, `user_id`, `name`, `name_rw`, `price`, `unit`, `category`, `location`, `image_type`, `image_path`, `is_certified`, `is_organic`, `created_at`) VALUES ('10', '1', 'Tomato', 'Tomato', '180.00', 'kg', 'Produce', 'Bugesera', 'produce', 'uploads/products/prod_1765806585_8431.jpg', '1', '0', '2025-12-15 13:49:45');
INSERT INTO `products` (`id`, `user_id`, `name`, `name_rw`, `price`, `unit`, `category`, `location`, `image_type`, `image_path`, `is_certified`, `is_organic`, `created_at`) VALUES ('11', '4', 'Straw berries', 'Straw berries', '750.00', 'kg', 'Produce', 'Kigali', 'produce', 'uploads/products/prod_1765806790_2617.jpg', '1', '0', '2025-12-15 13:53:10');
INSERT INTO `products` (`id`, `user_id`, `name`, `name_rw`, `price`, `unit`, `category`, `location`, `image_type`, `image_path`, `is_certified`, `is_organic`, `created_at`) VALUES ('12', '4', 'Orange', 'Orange', '170.00', 'kg', 'Produce', 'Kigali', 'produce', 'uploads/products/prod_1765807246_3494.jpg', '1', '0', '2025-12-15 14:00:46');
INSERT INTO `products` (`id`, `user_id`, `name`, `name_rw`, `price`, `unit`, `category`, `location`, `image_type`, `image_path`, `is_certified`, `is_organic`, `created_at`) VALUES ('13', '4', 'Pineaple', 'Pineaple', '500.00', 'kg', 'Produce', 'Kigali', 'produce', 'uploads/products/prod_1765807281_8018.jpg', '1', '0', '2025-12-15 14:01:21');
INSERT INTO `products` (`id`, `user_id`, `name`, `name_rw`, `price`, `unit`, `category`, `location`, `image_type`, `image_path`, `is_certified`, `is_organic`, `created_at`) VALUES ('14', '4', 'Mangoes', 'Mangoes', '350.00', 'kg', 'Produce', 'Kigali', 'produce', 'uploads/products/prod_1765807349_9365.jpg', '1', '0', '2025-12-15 14:02:29');


CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES ('1', '4', '4', '5', 'this product is good', '2025-12-14 22:36:33');


CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('farmer','buyer') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `bio` text DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `first_name`, `last_name`, `phone`, `email`, `profile_picture`, `password`, `role`, `created_at`, `bio`, `address`) VALUES ('1', 'Jean', 'Paul', NULL, 'farmer@gmail.com', NULL, '$2y$10$b3OHWcet.k7veoPors8MvurQihDIluf9q2TzVWb6HDRgrBgU8w04G', 'farmer', '2025-12-12 13:12:46', NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `phone`, `email`, `profile_picture`, `password`, `role`, `created_at`, `bio`, `address`) VALUES ('2', 'Alice', 'Uwase', NULL, 'buyer@gmail.com', NULL, '$2y$10$b3OHWcet.k7veoPors8MvurQihDIluf9q2TzVWb6HDRgrBgU8w04G', 'buyer', '2025-12-12 13:12:46', NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `phone`, `email`, `profile_picture`, `password`, `role`, `created_at`, `bio`, `address`) VALUES ('3', 'Olivier', 'KWIZERA', NULL, 'olivierkwizera999@gmail.com', NULL, '$2y$10$nS4e/NImCRjRZ83p438dTesh0v7IuXj1FDp0yF8nNI3CXyt0nkLK.', 'buyer', '2025-12-12 13:14:24', NULL, NULL);
INSERT INTO `users` (`id`, `first_name`, `last_name`, `phone`, `email`, `profile_picture`, `password`, `role`, `created_at`, `bio`, `address`) VALUES ('4', 'olly', 'shavez', NULL, 'ollyshavez@gmail.com', 'uploads/profiles/user_4_1765546743.png', '$2y$10$AHwH44Kw/zr8IanMqJ2wouoNq9uJvyfpZjRUnGeKXlAhyYBD/lG9y', 'farmer', '2025-12-12 13:33:39', NULL, NULL);


CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES ('4', '4', '4', '2025-12-14 22:36:08');
