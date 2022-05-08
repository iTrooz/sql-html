create table test.pages
(
    pageID int auto_increment
        primary key,
    uri    text not null,
    constraint pages_uri_uindex
        unique (uri) using hash
);

create table test.nodes
(
    nodeID       int auto_increment
        primary key,
    pageID       int  not null,
    tagName      text not null,
    tagValue     text null,
    parentNodeID int  null,
    constraint nodes_fk
        foreign key (parentNodeID) references test.nodes (nodeID)
            on delete cascade,
    constraint nodes_pages_pageID_fk
        foreign key (pageID) references test.pages (pageID)
            on delete cascade
);

create table test.attrs
(
    attrID    int auto_increment
        primary key,
    nodeID    int  null,
    attrName  text null,
    attrValue text null,
    constraint attributes_nodes_nodeID_fk
        foreign key (nodeID) references test.nodes (nodeID)
            on delete cascade
);
