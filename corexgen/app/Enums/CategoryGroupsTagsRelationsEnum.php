<?php

enum CategoryGroupsTagsRelationsEnum: string
{
    // relationsships
    case Clients = 'clients';
    case Users = 'users';
    case Companies = 'companies';
    case Roles = 'roles';


    // types
    case Categories = 'categories'; 

    case Tags = 'tags';

    case Groups = 'groups';
}
