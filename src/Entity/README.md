Currently, only pages (or SandboxPage) has been implemented.

sqlite> .schema
CREATE TABLE resource_types (
  type char(3) PRIMARY KEY
);
CREATE TABLE pages (
  id INTEGER PRIMARY KEY,
  html text NOT NULL
);
CREATE TABLE resources (
  id INTEGER PRIMARY KEY,
  filename varchar(45) NOT NULL,
  type char(3) NOT NULL,
  date_created DATETIME DEFAULT CURRENT_TIMESTAMP,
  size int(11) NOT NULL,
  CONSTRAINT fk_resources_types FOREIGN KEY (type) REFERENCES resource_types (type) ON DELETE NO ACTION ON UPDATE NO ACTION
);
CREATE TABLE pages_has_resources (
  pages_id int(11) NOT NULL,
  resources_id int(11) NOT NULL,
  date_added DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (pages_id,resources_id),
  CONSTRAINT fk_pages_has_resources_pages FOREIGN KEY (pages_id) REFERENCES pages (id) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT fk_pages_has_resources_resources FOREIGN KEY (resources_id) REFERENCES resources (id) ON DELETE CASCADE ON UPDATE NO ACTION
);
sqlite>
