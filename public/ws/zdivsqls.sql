/* 
 * Copyright (C) 20xx VMA Vincent Maury <vmaury@timgroup.fr>
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY
 */
/**
 * Author:  vmaury
 * Created: 2 oct. 2023
 */
delete from bolt_field_translation where translatable_id in 
(select bf.id from bolt_field bf where bf.content_id in (select id from bolt_content where content_type='chantiers'));

delete from bolt_field where content_id in (select id from bolt_content where content_type='chantiers');
delete from bolt_taxonomy_content where content_id in (select id from bolt_content where content_type='chantiers');
delete from bolt_content where content_type='chantiers';