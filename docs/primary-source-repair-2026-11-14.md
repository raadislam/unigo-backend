Production source repair for South Korea and Singapore

The backend deployment now runs VisaPrimarySourceRepairSeeder after the existing visa checklist seeders. It attaches the official Embassy of the Republic of Korea in Bangladesh tourist visa checklist as the primary official source for KR and the High Commission of Singapore in Dhaka visa application page as the primary official source for SG. Official VFS and ICA links are retained as supporting cross-check sources. The seeder only updates existing verified, published BGD tourism records and is idempotent.
