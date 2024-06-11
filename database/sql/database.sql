-- convert Laravel migrations to raw SQL scripts --

-- migration:2024_06_07_174353_create_responses_table --
create table `responses` (
  `id` bigint unsigned not null auto_increment primary key, 
  `session_id` bigint unsigned not null, 
  `question_id` bigint unsigned not null, 
  `option_id` bigint unsigned not null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table 
  `responses` 
add 
  constraint `responses_session_id_foreign` foreign key (`session_id`) references `assignment_user` (`id`) on delete cascade;
alter table 
  `responses` 
add 
  constraint `responses_question_id_foreign` foreign key (`question_id`) references `quiz_questions` (`id`) on delete cascade;
alter table 
  `responses` 
add 
  constraint `responses_option_id_foreign` foreign key (`option_id`) references `v` (`id`) on delete cascade;
