-- Database creation script for BOOK BAZAAR
-- File: bookstore.sql
-- Run this script in phpMyAdmin, XAMPP, or directly in your MySQL shell to set up the schema.

CREATE DATABASE IF NOT EXISTS `bookstore`;
USE `bookstore`;

-- Drop tables if they exist to prevent schema conflicts during fresh setup
DROP TABLE IF EXISTS `comments`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `browsing_history`;
DROP TABLE IF EXISTS `purchases`;
DROP TABLE IF EXISTS `cart`;
DROP TABLE IF EXISTS `books`;
DROP TABLE IF EXISTS `users`;

-- 1. Users Table
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(150) NOT NULL UNIQUE,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password_hash` VARCHAR(256) NOT NULL,
    `profile_picture` VARCHAR(255) DEFAULT NULL,
    `is_approved` TINYINT(1) NOT NULL DEFAULT 0,
    `preferred_categories` VARCHAR(500) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default administrator account (Password: admin123, pre-approved)
INSERT INTO `users` (`username`, `email`, `password_hash`, `is_approved`) VALUES
('admin', 'admin@bookbazaar.com', '$2y$10$6p628x.L06wPaCd1JddPzOnSD12eYIBWcXbUtH9Ds9yC.CkcbH2Yq', 1);

-- 2. Books Table (Includes category column)
CREATE TABLE `books` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `author` VARCHAR(255) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `description` TEXT NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `cover_image` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Cart Table
CREATE TABLE `cart` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `book_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `added_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Purchases Table (Order tracking system)
CREATE TABLE `purchases` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `book_id` INT NOT NULL,
    `quantity` INT NOT NULL,
    `price_paid` DECIMAL(10,2) NOT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'Pending',
    `purchased_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Browsing History Table (Personalized recommendation tracking)
CREATE TABLE `browsing_history` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `book_id` INT NOT NULL,
    `viewed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Reviews Table (Verified purchase feedback)
CREATE TABLE `reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `book_id` INT NOT NULL,
    `rating` INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    `review_text` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Comments Table (Discussion Board)
CREATE TABLE `comments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `book_id` INT NOT NULL,
    `comment_text` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seeding Book Data (108 books, 12 per category across 9 categories)
INSERT INTO `books` (`title`, `author`, `price`, `description`, `category`, `cover_image`) VALUES
-- Category: Romance
('Pride and Prejudice', 'Jane Austen', 4500.00, 'A classic romance novel focusing on Elizabeth Bennet and Mr. Darcy as they navigate pride, social class, and love.', 'Romance', 'romance_1.png'),
('The Fault in Our Stars', 'John Green', 6000.00, 'A tragic love story of two teenagers, Hazel and Augustus, who meet at a cancer support group and share a deep connection.', 'Romance', 'romance_2.png'),
('Jane Eyre', 'Charlotte Brontë', 5200.00, 'A gothic romance exploring the life and struggles of Jane Eyre as she finds passion and secrets with Edward Rochester.', 'Romance', 'romance_3.png'),
('The Notebook', 'Nicholas Sparks', 5800.00, 'The enduring love story of Noah and Allie, spanning decades from their youth to their twilight years.', 'Romance', 'romance_4.png'),
('Romeo and Juliet', 'William Shakespeare', 3000.00, 'The iconic tragic play of two star-crossed young lovers from rival noble families in Verona.', 'Romance', 'romance_5.png'),
('Sense and Sensibility', 'Jane Austen', 4800.00, 'Follows the lives of the Dashwood sisters as they deal with romance, loss of status, and emotional choices.', 'Romance', 'romance_6.png'),
('Wuthering Heights', 'Emily Brontë', 5500.00, 'A turbulent tale of intense, destructive passion between Heathcliff and Catherine Earnshaw on the Yorkshire moors.', 'Romance', 'romance_7.png'),
('The Wedding Date', 'Jasmine Guillory', 7500.00, 'A fun, contemporary romance that starts when two strangers agree to go to a wedding together.', 'Romance', 'romance_8.png'),
('Red, White & Royal Blue', 'Casey McQuiston', 8000.00, 'A delightful modern romance between the First Son of the United States and the Prince of Wales.', 'Romance', 'romance_9.png'),
('Me Before You', 'Jojo Moyes', 6800.00, 'An emotionally charged love story between Louisa Clark and Will Traynor, who is paralyzed after an accident.', 'Romance', 'romance_10.png'),
('Outlander', 'Diana Gabaldon', 9000.00, 'A time-travel historical romance following Claire Randall as she is swept from 1945 to 18th-century Scotland.', 'Romance', 'romance_11.png'),
('Normal People', 'Sally Rooney', 8500.00, 'An intimate look at the complex, changing relationship of Marianne and Connell as they grow into adulthood.', 'Romance', 'romance_12.png'),

-- Category: Fantasy
('The Hobbit', 'J.R.R. Tolkien', 8500.00, 'Bilbo Baggins is swept into a quest to reclaim the lost Dwarf Kingdom of Erebor from the dragon Smaug.', 'Fantasy', 'fantasy_1.png'),
('Harry Potter and the Sorcerer\'s Stone', 'J.K. Rowling', 9500.00, 'A young wizard discovers his magical heritage and enters Hogwarts School of Witchcraft and Wizardry.', 'Fantasy', 'fantasy_2.png'),
('The Way of Kings', 'Brandon Sanderson', 18000.00, 'An epic fantasy set on the storm-swept world of Roshar, detailing the war for survival and honor.', 'Fantasy', 'fantasy_3.png'),
('A Game of Thrones', 'George R.R. Martin', 15000.00, 'Noble houses fight for control of the Iron Throne of Westeros in a world of intrigue and impending winter.', 'Fantasy', 'fantasy_4.png'),
('The Name of the Wind', 'Patrick Rothfuss', 12000.00, 'The life history of Kvothe, a legendary wizard and musician, told in his own words.', 'Fantasy', 'fantasy_5.png'),
('Mistborn: The Final Empire', 'Brandon Sanderson', 11000.00, 'A crew of thieves plans to overthrow a thousand-year-old immortal ruler in a world blanketed by ash.', 'Fantasy', 'fantasy_6.png'),
('The Fellowship of the Ring', 'J.R.R. Tolkien', 9000.00, 'The dark power of the Dark Lord Sauron rises, and the Ringbearer Frodo Baggins sets off to destroy the One Ring.', 'Fantasy', 'fantasy_7.png'),
('Percy Jackson & The Lightning Thief', 'Rick Riordan', 6500.00, 'A modern boy discovers he is a demigod son of Poseidon and must stop a war among the Greek gods.', 'Fantasy', 'fantasy_8.png'),
('American Gods', 'Neil Gaiman', 8000.00, 'An ex-convict becomes the bodyguard of a mysterious man named Wednesday, caught in a battle of ancient and modern gods.', 'Fantasy', 'fantasy_9.png'),
('The Priory of the Orange Tree', 'Samantha Shannon', 16500.00, 'An epic, standalone fantasy of queens, dragon riders, and mages trying to prevent the return of a primordial threat.', 'Fantasy', 'fantasy_10.png'),
('The Blade Itself', 'Joe Abercrombie', 10500.00, 'A gritty, character-driven fantasy where war looms and complex antiheroes clash in a harsh world.', 'Fantasy', 'fantasy_11.png'),
('The Lies of Locke Lamora', 'Scott Lynch', 11500.00, 'Follows Locke Lamora and his crew of Gentlemen Bastards as they con the nobility in the city of Camorr.', 'Fantasy', 'fantasy_12.png'),

-- Category: Science Fiction
('Dune', 'Frank Herbert', 12500.00, 'The landmark sci-fi epic set on the desert planet Arrakis, focusing on politics, religion, and Paul Atreides.', 'Science Fiction', 'scifi_1.png'),
('Neuromancer', 'William Gibson', 9800.00, 'The seminal cyberpunk novel that popularized the concept of cyberspace and artificial intelligence grids.', 'Science Fiction', 'scifi_2.png'),
('Foundation', 'Isaac Asimov', 8800.00, 'A mathematician predicts the fall of the Galactic Empire and creates a foundation to preserve human knowledge.', 'Science Fiction', 'scifi_3.png'),
('1984', 'George Orwell', 4000.00, 'A dystopian novel detailing the dangers of totalitarianism, surveillance, and absolute government control.', 'Science Fiction', 'scifi_4.png'),
('The Martian', 'Andy Weir', 8500.00, 'An astronaut stranded on Mars must use his intelligence and scientific skills to survive until a rescue mission arrives.', 'Science Fiction', 'scifi_5.png'),
('Snow Crash', 'Neal Stephenson', 10500.00, 'A fast-paced cyberpunk novel exploring virtual reality, corporate franchises, and linguistic viruses.', 'Science Fiction', 'scifi_6.png'),
('Brave New World', 'Aldous Huxley', 5000.00, 'Explores a futuristic society controlled by reproductive technology, psychological conditioning, and drug soma.', 'Science Fiction', 'scifi_7.png'),
('Fahrenheit 451', 'Ray Bradbury', 4500.00, 'Depicts a future society where books are outlawed and "firemen" burn any they find to suppress critical thinking.', 'Science Fiction', 'scifi_8.png'),
('Hyperion', 'Dan Simmons', 11000.00, 'Seven pilgrims travel to the mysterious Time Tombs on the planet Hyperion to seek the legendary Shrike.', 'Science Fiction', 'scifi_9.png'),
('Ender\'s Game', 'Orson Scott Card', 7500.00, 'A young boy is recruited to a military space school to train for an impending alien invasion.', 'Science Fiction', 'scifi_10.png'),
('The Left Hand of Darkness', 'Ursula K. Le Guin', 9000.00, 'A human envoy travels to a winter world populated by gender-fluid humanoids to invite them to a galactic alliance.', 'Science Fiction', 'scifi_11.png'),
('Station Eleven', 'Emily St. John Mandel', 8000.00, 'A haunting post-apocalyptic novel tracing the lives of survivors before and after a devastating flu pandemic.', 'Science Fiction', 'scifi_12.png'),

-- Category: Horror
('Dracula', 'Bram Stoker', 4800.00, 'The classic vampire tale told through diaries and letters, following Count Dracula\'s attempt to move to England.', 'Horror', 'horror_1.png'),
('The Shining', 'Stephen King', 9500.00, 'An alcoholic writer becomes the winter caretaker of the haunted Overlook Hotel, threatening his family.', 'Horror', 'horror_2.png'),
('Frankenstein', 'Mary Shelley', 4500.00, 'A young scientist creates a sentient monster in an unorthodox scientific experiment, facing tragic consequences.', 'Horror', 'horror_3.png'),
('It', 'Stephen King', 14000.00, 'Seven children are terrorized by an ancient, shape-shifting entity that exploits their deepest fears in Derry.', 'Horror', 'horror_4.png'),
('Pet Sematary', 'Stephen King', 8800.00, 'A family moves to a rural home near a mysterious burial ground that brings dead pets, and later humans, back to life.', 'Horror', 'horror_5.png'),
('Bird Box', 'Josh Malerman', 7200.00, 'A mother and her children must travel down a river blindfolded to escape mysterious entities that drive people mad upon sight.', 'Horror', 'horror_6.png'),
('The Haunting of Hill House', 'Shirley Jackson', 6800.00, 'Four people investigate paranormal activity in Hill House, a mansion with a dark, psychological presence.', 'Horror', 'horror_7.png'),
('House of Leaves', 'Mark Z. Danielewski', 15500.00, 'A family discovers their house is inexplicably larger on the inside than it is on the outside, leading to madness.', 'Horror', 'horror_8.png'),
('Dr. Jekyll and Mr. Hyde', 'Robert Louis Stevenson', 3500.00, 'A gothic novella investigating the dual nature of man and the split personalities of Jekyll and Hyde.', 'Horror', 'horror_9.png'),
('The Silence of the Lambs', 'Thomas Harris', 8000.00, 'FBI trainee Clarice Starling seeks the help of imprisoned cannibal Hannibal Lecter to catch a serial killer.', 'Horror', 'horror_10.png'),
('Heart-Shaped Box', 'Joe Hill', 9000.00, 'An aging rock star buys a ghost on an online auction site, only to find himself stalked by a vengeful spirit.', 'Horror', 'horror_11.png'),
('The Exorcist', 'William Peter Blatty', 8500.00, 'A desperate mother seeks the help of two priests to perform an exorcism on her demonically possessed daughter.', 'Horror', 'horror_12.png'),

-- Category: Mystery & Thriller
('The Da Vinci Code', 'Dan Brown', 8500.00, 'A murder in the Louvre leads symbologist Robert Langdon and cryptologist Sophie Neveu on a quest to solve secret codes.', 'Mystery & Thriller', 'mystery_1.png'),
('Gone Girl', 'Gillian Flynn', 7800.00, 'On their fifth wedding anniversary, Amy Dunne disappears, making her husband Nick the prime suspect.', 'Mystery & Thriller', 'mystery_2.png'),
('The Girl with the Dragon Tattoo', 'Stieg Larsson', 9500.00, 'A journalist and a brilliant computer hacker investigate the decades-old disappearance of a wealthy family\'s niece.', 'Mystery & Thriller', 'mystery_3.png'),
('And Then There Were None', 'Agatha Christie', 5000.00, 'Ten strangers are invited to an isolated island and accused of murder, dying one by one according to a nursery rhyme.', 'Mystery & Thriller', 'mystery_4.png'),
('Sherlock Holmes: Selected Stories', 'Arthur Conan Doyle', 6000.00, 'A collection of the most famous cases solved by detective Sherlock Holmes and his assistant Dr. John Watson.', 'Mystery & Thriller', 'mystery_5.png'),
('Big Little Lies', 'Liane Moriarty', 7500.00, 'Follows three mothers in a coastal community whose seemingly perfect lives unravel, leading to murder.', 'Mystery & Thriller', 'mystery_6.png'),
('The Silent Patient', 'Alex Michaelides', 8200.00, 'A woman shoots her husband and never speaks another word, challenging a psychotherapist to uncover her motive.', 'Mystery & Thriller', 'mystery_7.png'),
('The Woman in the Window', 'A.J. Finn', 7000.00, 'An agoraphobic woman believes she witnessed a crime in her neighbor\'s house, but no one believes her.', 'Mystery & Thriller', 'mystery_8.png'),
('The Guest List', 'Lucy Foley', 8000.00, 'A glamorous wedding on a remote Irish island turns deadly as old resentments arise and a guest is murdered.', 'Mystery & Thriller', 'mystery_9.png'),
('In the Woods', 'Tana French', 9000.00, 'A detective investigates the murder of a young girl in the same woods where his childhood friends disappeared.', 'Mystery & Thriller', 'mystery_10.png'),
('The Girl on the Train', 'Paula Hawkins', 7500.00, 'An alcoholic commuter gets caught up in a missing persons investigation after observing a couple from the train.', 'Mystery & Thriller', 'mystery_11.png'),
('Shutter Island', 'Dennis Lehane', 8500.00, 'Two US Marshals travel to a hospital for the criminally insane on Shutter Island to investigate a patient\'s escape.', 'Mystery & Thriller', 'mystery_12.png'),

-- Category: Literary Fiction
('The Great Gatsby', 'F. Scott Fitzgerald', 4000.00, 'Explores themes of wealth, love, and the American Dream through the life of Jay Gatsby and his obsession with Daisy Buchanan.', 'Literary Fiction', 'literary_1.png'),
('To Kill a Mockingbird', 'Harper Lee', 5500.00, 'A child\'s perspective on racial injustice and moral growth in a small Southern town during the Great Depression.', 'Literary Fiction', 'literary_2.png'),
('The Catcher in the Rye', 'J.D. Salinger', 5000.00, 'Follows a disillusioned teenager, Holden Caulfield, as he wanders New York City, exploring alienation.', 'Literary Fiction', 'literary_3.png'),
('Beloved', 'Toni Morrison', 7800.00, 'A powerful, Pulitzer Prize-winning novel about a former slave haunted by the ghost of her daughter.', 'Literary Fiction', 'literary_4.png'),
('One Hundred Years of Solitude', 'Gabriel García Márquez', 9800.00, 'The multi-generational story of the Buendía family in the fictional, isolated town of Macondo.', 'Literary Fiction', 'literary_5.png'),
('The Picture of Dorian Gray', 'Oscar Wilde', 4500.00, 'A philosophical novel about a man who remains youthful while his portrait ages and reflects his moral decay.', 'Literary Fiction', 'literary_6.png'),
('The Alchemist', 'Paulo Coelho', 4200.00, 'The allegorical journey of an Andalusian shepherd boy who travels to Egypt in search of a worldly treasure.', 'Literary Fiction', 'literary_7.png'),
('Life of Pi', 'Yann Martel', 6500.00, 'An Indian boy survives a shipwreck and is stranded in the Pacific Ocean on a lifeboat with a Bengal tiger.', 'Literary Fiction', 'literary_8.png'),
('The Kite Runner', 'Khaled Hosseini', 7000.00, 'A story of friendship, betrayal, and redemption set against the backdrop of Afghanistan\'s turbulent history.', 'Literary Fiction', 'literary_9.png'),
('A Thousand Splendid Suns', 'Khaled Hosseini', 8000.00, 'Chronicles the lives of two Afghan women, Mariam and Laila, as their lives intersect during decades of war.', 'Literary Fiction', 'literary_10.png'),
('The Book Thief', 'Markus Zusak', 7500.00, 'Narrated by Death, follows Liesel Meminger as she steals books and shares them with others in Nazi Germany.', 'Literary Fiction', 'literary_11.png'),
('Things Fall Apart', 'Chinua Achebe', 3500.00, 'Chronicles the life of Okonkwo, an Igbo leader, and the devastating impact of British colonialism in Nigeria.', 'Literary Fiction', 'literary_12.png'),

-- Category: Biography & Memoir
('Steve Jobs', 'Walter Isaacson', 15000.00, 'The comprehensive biography of Apple co-founder Steve Jobs, based on interviews with Jobs and those who knew him.', 'Biography & Memoir', 'bio_1.png'),
('Becoming', 'Michelle Obama', 12500.00, 'A deeply personal memoir by the former First Lady of the United States, tracing her journey from Chicago to the White House.', 'Biography & Memoir', 'bio_2.png'),
('The Diary of a Young Girl', 'Anne Frank', 4000.00, 'The writings of a Jewish teenager who went into hiding with her family during the Nazi occupation of the Netherlands.', 'Biography & Memoir', 'bio_3.png'),
('Educated', 'Tara Westover', 9500.00, 'A memoir about a young woman who leaves her survivalist family in Idaho to pursue an education, eventually earning a PhD.', 'Biography & Memoir', 'bio_4.png'),
('Long Walk to Freedom', 'Nelson Mandela', 11000.00, 'The autobiography of South African anti-apartheid revolutionary Nelson Mandela, charting his struggle and presidency.', 'Biography & Memoir', 'bio_5.png'),
('Shoe Dog', 'Phil Knight', 10000.00, 'The founder of Nike shares the inside story of the company\'s early days as an intrepid start-up.', 'Biography & Memoir', 'bio_6.png'),
('Born a Crime', 'Trevor Noah', 8500.00, 'A humorous and moving memoir of Trevor Noah\'s childhood growing up in South Africa under Apartheid.', 'Biography & Memoir', 'bio_7.png'),
('Unbroken', 'Laura Hillenbrand', 9000.00, 'The biography of Louis Zamperini, an Olympic runner who became a Japanese prisoner of war during WWII.', 'Biography & Memoir', 'bio_8.png'),
('The Glass Castle', 'Jeannette Walls', 7800.00, 'A remarkable memoir of resilience, detailing the author\'s childhood growing up with eccentric, nomadic parents.', 'Biography & Memoir', 'bio_9.png'),
('Leonardo da Vinci', 'Walter Isaacson', 14000.00, 'A fascinating biography of Leonardo da Vinci, demonstrating how his curiosity and scientific pursuits fed his art.', 'Biography & Memoir', 'bio_10.png'),
('When Breath Becomes Air', 'Paul Kalanithi', 8000.00, 'A neurosurgeon diagnosed with terminal lung cancer writes a moving meditation on what makes a life worth living.', 'Biography & Memoir', 'bio_11.png'),
('I Know Why the Caged Bird Sings', 'Maya Angelou', 6800.00, 'A beautifully written autobiography detailing Maya Angelou\'s early years, focusing on themes of racism and identity.', 'Biography & Memoir', 'bio_12.png'),

-- Category: Health & Awareness
('The Emperor of All Maladies', 'Siddhartha Mukherjee', 16000.00, 'A comprehensive "biography" of cancer, tracing its history from ancient times to modern medical treatments.', 'Health & Awareness', 'health_1.png'),
('Why We Sleep', 'Matthew Walker', 11500.00, 'Explores the scientific importance of sleep and how it affects cognitive capacity, physical health, and longevity.', 'Health & Awareness', 'health_2.png'),
('Breath: The New Science of a Lost Art', 'James Nestor', 9800.00, 'Investigates how humans have lost the ability to breathe correctly and how making small adjustments can transform health.', 'Health & Awareness', 'health_3.png'),
('How Not to Die', 'Michael Greger', 14500.00, 'Examines the scientific evidence behind a plant-based diet to prevent and reverse the leading causes of death.', 'Health & Awareness', 'health_4.png'),
('Atomic Habits', 'James Clear', 9000.00, 'An easy, practical guide to building good habits and breaking bad ones, backed by behavioral psychology.', 'Health & Awareness', 'health_5.png'),
('The Body Keeps the Score', 'Bessel van der Kolk', 12000.00, 'Explores how trauma rearranges the brain and body and details innovative paths to recovery.', 'Health & Awareness', 'health_6.png'),
('Thinking, Fast and Slow', 'Daniel Kahneman', 11000.00, 'A detailed explanation of the two systems that drive our thoughts: System 1 (fast/intuitive) and System 2 (slow/logical).', 'Health & Awareness', 'health_7.png'),
('Outlive: The Science and Art of Longevity', 'Peter Attia', 18500.00, 'A groundbreaking guide to longevity, detailing physical, nutritional, and emotional strategies for a healthier lifespan.', 'Health & Awareness', 'health_8.png'),
('Grit', 'Angela Duckworth', 9500.00, 'Argues that the secret to outstanding achievement is not talent, but a unique blend of passion and persistence.', 'Health & Awareness', 'health_9.png'),
('Quiet: The Power of Introverts', 'Susan Cain', 8800.00, 'Advocates for introverts in a world that can\'t stop talking, showing how introversion drives creativity and leadership.', 'Health & Awareness', 'health_10.png'),
('The Power of Habit', 'Charles Duhigg', 8500.00, 'Investigates the science of habit formation and how habits can be changed in our lives and businesses.', 'Health & Awareness', 'health_11.png'),
('Man\'s Search for Meaning', 'Viktor E. Frankl', 4500.00, 'A psychiatrist\'s memoir detailing his survival in Nazi concentration camps and his theory of logotherapy.', 'Health & Awareness', 'health_12.png'),

-- Category: Animation
('The Animator\'s Survival Kit', 'Richard Williams', 22000.00, 'The definitive handbook for animators, covering principles of movement, walks, runs, and dialogue animation.', 'Animation', 'anim_1.png'),
('Disney Animation: The Illusion of Life', 'Frank Thomas & Ollie Johnston', 35000.00, 'Written by Disney legends, explains the 12 basic principles of hand-drawn character animation.', 'Animation', 'anim_2.png'),
('Framed Ink: Drawing and Composition', 'Marcos Mateu-Mestre', 15500.00, 'An essential guide to drawing, composition, and visual storytelling for comic books, storyboards, and film.', 'Animation', 'anim_3.png'),
('Character Animation Crash Course!', 'Eric Goldberg', 18000.00, 'A comprehensive guide to classic character animation techniques, full of tips from a legendary Disney animator.', 'Animation', 'anim_4.png'),
('Timing for Animation', 'Harold Whitaker & John Halas', 14000.00, 'Explains the crucial role of timing in character animation, cartoon gags, and dramatic actions.', 'Animation', 'anim_5.png'),
('Directing the Story', 'Francis Glebas', 16500.00, 'Teaches structural screenwriting, layout, and storyboarding techniques to direct the viewer\'s eye and emotions.', 'Animation', 'anim_6.png'),
('Force: Dynamic Life Drawing for Animators', 'Mike Mattesi', 13500.00, 'Focuses on drawing human figures with dynamic energy, line of action, and organic force for animators.', 'Animation', 'anim_7.png'),
('Cartoon Animation', 'Preston Blair', 9500.00, 'A classic cartoon drawing guide covering character construction, walk cycles, and facial expressions.', 'Animation', 'anim_8.png'),
('Storyball', 'John Hart', 11000.00, 'Discusses storyboarding principles and pre-visualization workflows for animated features and live-action films.', 'Animation', 'anim_9.png'),
('Layout and Composition for Animation', 'Ed Ghertner', 15000.00, 'Focuses on background design, perspective grids, camera angles, and layout composition for animation.', 'Animation', 'anim_10.png'),
('Pixar Storytelling', 'Dean Movshovitz', 8000.00, 'Analyzes Pixar\'s narrative rules and storytelling elements that create beloved, emotionally resonant films.', 'Animation', 'anim_11.png'),
('Creating Characters with Personality', 'Tom Bancroft', 12500.00, 'An animation designer teaches how to brainstorm, sketch, and refine expressive characters with unique personalities.', 'Animation', 'anim_12.png');
