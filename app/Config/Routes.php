<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/ppdb', 'Ppdb::index');
$routes->get('/admin', 'Auth::login');
$routes->get('/admin/login', 'Auth::login');
$routes->post('/admin/login', 'Auth::attemptLogin');
$routes->get('/admin/logout', 'Auth::logout');
$routes->post('/chatbot', 'Chatbot::chat');
$routes->get('/dashboard', 'Dashboard::index');
$routes->get('/dashboard/profile', 'Dashboard::profile');
$routes->post('/dashboard/profile', 'Dashboard::updateProfile');
$routes->get('/dashboard/scan-whatsapp', 'Dashboard::scanWhatsapp');
$routes->get('/dashboard/support-chat', 'Dashboard::supportChat');
$routes->get('/dashboard/history-chat', 'Dashboard::historyChat');
$routes->get('/dashboard/intents', 'Dashboard::intents');
$routes->get('/dashboard/intents/create', 'Dashboard::createIntent');
$routes->post('/dashboard/intents', 'Dashboard::storeIntent');
$routes->get('/dashboard/intents/(:num)/edit', 'Dashboard::editIntent/$1');
$routes->post('/dashboard/intents/(:num)', 'Dashboard::updateIntent/$1');
$routes->post('/dashboard/intents/(:num)/toggle', 'Dashboard::toggleKnowledgeBase/$1');
$routes->post('/dashboard/intents/(:num)/delete', 'Dashboard::deleteIntent/$1');
$routes->get('/dashboard/training-phrases', 'Dashboard::trainingPhrases');
$routes->post('/dashboard/training-phrases', 'Dashboard::storeTrainingPhrase');
$routes->post('/dashboard/training-phrases/(:num)', 'Dashboard::updateTrainingPhrase/$1');
$routes->post('/dashboard/training-phrases/(:num)/delete', 'Dashboard::deleteTrainingPhrase/$1');
$routes->get('/dashboard/keywords', 'Dashboard::keywords');
$routes->post('/dashboard/keywords', 'Dashboard::storeKeyword');
$routes->post('/dashboard/keywords/(:num)', 'Dashboard::updateKeyword/$1');
$routes->post('/dashboard/keywords/(:num)/delete', 'Dashboard::deleteKeyword/$1');
$routes->get('/dashboard/knowledge-base', 'Dashboard::knowledgeBase');
$routes->get('/dashboard/knowledge-base/create', 'Dashboard::createKnowledgeBase');
$routes->post('/dashboard/knowledge-base', 'Dashboard::storeKnowledgeBase');
$routes->get('/dashboard/knowledge-base/(:num)/edit', 'Dashboard::editKnowledgeBase/$1');
$routes->post('/dashboard/knowledge-base/(:num)', 'Dashboard::updateKnowledgeBase/$1');
$routes->post('/dashboard/knowledge-base/(:num)/toggle', 'Dashboard::toggleKnowledgeBase/$1');
$routes->post('/dashboard/knowledge-base/(:num)/delete', 'Dashboard::deleteKnowledgeBase/$1');
$routes->get('/dashboard/nlp-rules', 'Dashboard::nlpRules');
$routes->post('/dashboard/nlp-rules/(:segment)', 'Dashboard::storeNlpRule/$1');
$routes->post('/dashboard/nlp-rules/(:segment)/(:num)', 'Dashboard::updateNlpRule/$1/$2');
$routes->post('/dashboard/nlp-rules/(:segment)/(:num)/delete', 'Dashboard::deleteNlpRule/$1/$2');

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
