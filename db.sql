CREATE DATABASE IF NOT EXISTS yandex_music;

USE yandex_music;

CREATE TABLE IF NOT EXISTS artists (
  id INT UNSIGNED PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  subscribers INT UNSIGNED NOT NULL,
  monthly_listeners INT UNSIGNED NOT NULL,
  albums_count SMALLINT UNSIGNED NOT NULL
);

CREATE TABLE IF NOT EXISTS tracks (
  id INT UNSIGNED PRIMARY KEY,
  artist_id INT UNSIGNED REFERENCES artists ON DELETE CASCADE,
  name VARCHAR(255) NOT NULL,
  duration_ms SMALLINT UNSIGNED NOT NULL
);
