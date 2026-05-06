<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/ppdb', 'Ppdb::index');
$routes->get('/admin', 'Admin::index');
$routes->post('/chatbot', 'Chatbot::chat');
$routes->get('/dashboard', 'Dashboard::index');
$routes->get('/dashboard/scan-whatsapp', 'Dashboard::scanWhatsapp');
$routes->get('/dashboard/support-chat', 'Dashboard::supportChat');
$routes->get('/dashboard/history-chat', 'Dashboard::historyChat');

$routes->get('/api/wa/chats', 'Api\WhatsAppHistory::chats');
$routes->get('/api/wa/chats/(:num)', 'Api\WhatsAppHistory::chat/$1');
$routes->post('/api/wa/messages/incoming', 'Api\WhatsAppHistory::incoming');
$routes->post('/api/wa/messages/outgoing', 'Api\WhatsAppHistory::outgoing');
$routes->get('/api/wa/support-chats', 'Api\WhatsAppHistory::supportChats');
$routes->get('/api/wa/support-chats/open-chat/(:num)', 'Api\WhatsAppHistory::openSupportChatByChat/$1');
$routes->get('/api/wa/support-chats/open/(:any)', 'Api\WhatsAppHistory::openSupportChatByNumber/$1');
$routes->post('/api/wa/support-chats', 'Api\WhatsAppHistory::createSupportChat');
$routes->post('/api/wa/support-chats/(:num)/reply', 'Api\WhatsAppHistory::replySupportChat/$1');
$routes->post('/api/wa/support-chats/(:num)/end', 'Api\WhatsAppHistory::closeSupportChat/$1');
