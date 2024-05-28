-- convert Laravel migrations to raw SQL scripts --

-- migration:2024_05_22_130217_create_assignments_table --
create table `assignments` (
  `id` bigint unsigned not null auto_increment primary key, 
  `user_id` bigint unsigned not null, 
  `quiz_id` bigint unsigned not null, 
  `user_notified` tinyint(1) not null default '0', 
  `status_id` tinyint unsigned not null, 
  `assigned_at` timestamp not null default CURRENT_TIMESTAMP, 
  `assigned_by` bigint unsigned null, 
  `date_started` timestamp null default CURRENT_TIMESTAMP, 
  `date_completed` timestamp null default CURRENT_TIMESTAMP, 
  `score` int unsigned null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table 
  `assignments` 
add 
  constraint `assignments_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;
alter table 
  `assignments` 
add 
  constraint `assignments_quiz_id_foreign` foreign key (`quiz_id`) references `quizzes` (`id`) on delete cascade;
alter table 
  `assignments` 
add 
  constraint `assignments_status_id_foreign` foreign key (`status_id`) references `assignment_statuses` (`id`) on delete restrict;
alter table 
  `assignments` 
add 
  constraint `assignments_assigned_by_foreign` foreign key (`assigned_by`) references `users` (`id`) on delete 
set 
  null;

-- migration:2024_05_22_144853_create_assignment_statuses_table --
create table `assignment_statuses` (
  `id` tinyint unsigned not null auto_increment primary key, 
  `name` varchar(100) not null, 
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table 
  `assignment_statuses` 
add 
  unique `assignment_statuses_name_unique`(`name`);
