drop schema if exists messagerie cascade;
create schema messagerie;
set schema 'messagerie';



create table _user (
    nickname    varchar(30)  primary key,
    email       varchar(100) not null unique,
    pass        varchar(255) not null       
);

create table _document (
    iddoc       serial       primary key,
    content     text         not null,
    author      varchar(30)  not null references _user(nickname) on delete cascade,
    create_date timestamp    not null default now()
);

create table _message (
    iddoc                integer      primary key references _document(iddoc) on delete cascade,
    subject              varchar(100) not null,
    recipient            varchar(30)  not null references _user(nickname) on delete cascade,
    is_read              boolean      not null default false,
    deleted_by_author    boolean      not null default false,
    deleted_by_recipient boolean      not null default false
);


create or replace function f_user_normalise() returns trigger as $$
begin
    new.email := lower(trim(new.email));
    return new;
end;
$$ language plpgsql;

create trigger tg_user_normalise
    before insert or update on _user
    for each row execute procedure f_user_normalise();


create or replace function f_message_verif() returns trigger as $$
declare
    v_author varchar(30);
begin
    select author into v_author from messagerie._document where iddoc = new.iddoc;
    if v_author = new.recipient then
        raise 'Vous ne pouvez pas vous envoyer un message à vous-même.'
            using errcode = 'MS001';
    end if;
    return new;
end;
$$ language plpgsql;

create trigger tg_message_verif
    before insert on _message
    for each row execute procedure f_message_verif();


create or replace function f_document_orphelin() returns trigger as $$
begin
    if not exists (select 1 from messagerie._message where iddoc = new.iddoc) then
        raise 'Un document doit être associé à un message.'
            using errcode = 'MS002';
    end if;
    return null;
end;
$$ language plpgsql;

create constraint trigger tg_document_orphelin
    after insert on _document
    deferrable initially deferred
    for each row execute procedure f_document_orphelin();

-- ---------------------------------------------------------------------
--  Trigger 4 : suppression "logique".
--  Chacun (expéditeur / destinataire) supprime le message de SON côté.
--  Quand les deux l'ont supprimé, on le supprime réellement
--  (le on delete cascade supprime aussi la ligne de _message).
-- ---------------------------------------------------------------------
create or replace function f_message_nettoyage() returns trigger as $$
begin
    if new.deleted_by_author and new.deleted_by_recipient then
        delete from messagerie._document where iddoc = new.iddoc;
    end if;
    return null;
end;
$$ language plpgsql;

create trigger tg_message_nettoyage
    after update of deleted_by_author, deleted_by_recipient on _message
    for each row execute procedure f_message_nettoyage();

-- ---------------------------------------------------------------------
--  Fonction PL/pgSQL (cf. TD4 Ex.2) : nombre de messages non lus
--  Usage : select messagerie.nb_non_lus('Alice');
-- ---------------------------------------------------------------------
create or replace function nb_non_lus(p_nickname varchar) returns integer as $$
declare
    v_nb integer;
begin
    select count(*) into v_nb
      from messagerie._message
     where recipient = p_nickname
       and not is_read
       and not deleted_by_recipient;
    return v_nb;
end;
$$ language plpgsql;


create view message as
    select d.iddoc, m.subject, d.content, d.author, m.recipient,
           d.create_date, m.is_read,
           m.deleted_by_author, m.deleted_by_recipient
      from _document d
      join _message  m on m.iddoc = d.iddoc;


insert into _user(nickname, email, pass) values
 ('Laurent', 'Laurent@BigData.org', '$2y$10$1.1wQl50wqZUWNOyBdMzaO96A15RRXKQFiV/LPMTHC4ehRr6O2B2G'),

 ('Gilbert', 'gilbert@jujitsu.org', '$2y$10$1.1wQl50wqZUWNOyBdMzaO96A15RRXKQFiV/LPMTHC4ehRr6O2B2G'),

 ('Benoit',  'benoit@agile.org',    '$2y$10$1.1wQl50wqZUWNOyBdMzaO96A15RRXKQFiV/LPMTHC4ehRr6O2B2G');


begin;
insert into _document(content, author)
    values ('Salut Gilbert, on se fait un TP de BDD demain ?', 'Laurent');

insert into _message(iddoc, subject, recipient)
    values (currval('_document_iddoc_seq'), 'TP demain', 'Gilbert');
commit;

begin;
insert into _document(content, author)
    values ('Bienvenue sur la messagerie !', 'Benoit');
    
insert into _message(iddoc, subject, recipient)
    values (currval('_document_iddoc_seq'), 'Bienvenue', 'Laurent');
commit;
