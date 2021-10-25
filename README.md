# WooCommerce plugin for OSON

# Установка

<h4><a id="user-content-требования" class="anchor" aria-hidden="true" href="#требования"><svg class="octicon octicon-link" viewBox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg></a>Требования</h4>

<ul>
<li>PHP &gt;= 5.4</li>
<li><a href="https://wordpress.org/" rel="nofollow">WordPress</a></li>
<li><a href="https://woocommerce.com/" rel="nofollow">WooCommerce</a></li>
<li>Регистрация в кабинете поставщика <a href="https://business.oson.uz/" rel="nofollow">Business OSON</a></li>
</ul>
<h4><a id="user-content-github" class="anchor" aria-hidden="true" href="#github"><svg class="octicon octicon-link" viewBox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg></a>GitHub</h4>

<p>Скачайте плагин как <a href="https://github.com/Osonuz/Woocommerce-plugin/archive/refs/heads/main.zip">ZIP архив</a></p>

<p>Загрузите плагин в WordPress</p>

<p><a target="_blank" rel="noopener noreferrer" href="https://user-images.githubusercontent.com/92983919/138640691-afc645f5-f6dc-4f93-b587-4560f001cd3c.png"><img src="https://user-images.githubusercontent.com/92983919/138640691-afc645f5-f6dc-4f93-b587-4560f001cd3c.png" alt="Upload plugin" style="max-width: 100%;"></a></p>

<p>...и установите его</p>

<p><a target="_blank" rel="noopener noreferrer" href="https://user-images.githubusercontent.com/92983919/138671592-b4f91a0c-440f-4c81-8275-d775406a4914.png"><img src="https://user-images.githubusercontent.com/92983919/138671592-b4f91a0c-440f-4c81-8275-d775406a4914.png" alt="Install plugin from ZIP" style="max-width: 100%;"></a></p>

<p>Активируйте плагин после установки</p>
<p><a target="_blank" rel="noopener noreferrer" href="g"><img src="" alt="Activate plugin" style="max-width: 100%;"></a></p>

<h4><a id="user-content-что-бы-apache-не-игнорировал-заголовок-authorization-надо-загрузить-файл-htaccess-со-следующем-содержанием" class="anchor" aria-hidden="true" href="#что-бы-apache-не-игнорировал-заголовок-authorization-надо-загрузить-файл-htaccess-со-следующем-содержанием"><svg class="octicon octicon-link" viewBox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg></a>Что бы Apache не игнорировал заголовок <code>Authorization</code> надо загрузить файл <code>.htaccess</code> со следующем содержанием:</h4>

<div class="snippet-clipboard-content position-relative overflow-auto"><pre><code>RewriteEngine On
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]
</code></pre><div class="zeroclipboard-container position-absolute right-0 top-0">
    <clipboard-copy aria-label="Copy" class="ClipboardButton btn js-clipboard-copy m-2 p-0 tooltipped-no-delay" data-copy-feedback="Copied!" data-tooltip-direction="w" value="RewriteEngine On
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]
" tabindex="0" role="button">
      <svg aria-hidden="true" height="16" viewBox="0 0 16 16" version="1.1" width="16" data-view-component="true" class="octicon octicon-copy js-clipboard-copy-icon m-2">
    <path fill-rule="evenodd" d="M0 6.75C0 5.784.784 5 1.75 5h1.5a.75.75 0 010 1.5h-1.5a.25.25 0 00-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 00.25-.25v-1.5a.75.75 0 011.5 0v1.5A1.75 1.75 0 019.25 16h-7.5A1.75 1.75 0 010 14.25v-7.5z"></path><path fill-rule="evenodd" d="M5 1.75C5 .784 5.784 0 6.75 0h7.5C15.216 0 16 .784 16 1.75v7.5A1.75 1.75 0 0114.25 11h-7.5A1.75 1.75 0 015 9.25v-7.5zm1.75-.25a.25.25 0 00-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 00.25-.25v-7.5a.25.25 0 00-.25-.25h-7.5z"></path>
</svg>
      <svg aria-hidden="true" height="16" viewBox="0 0 16 16" version="1.1" width="16" data-view-component="true" class="octicon octicon-check js-clipboard-check-icon color-text-success d-none m-2">
    <path fill-rule="evenodd" d="M13.78 4.22a.75.75 0 010 1.06l-7.25 7.25a.75.75 0 01-1.06 0L2.22 9.28a.75.75 0 011.06-1.06L6 10.94l6.72-6.72a.75.75 0 011.06 0z"></path>
</svg>
    </clipboard-copy>
  </div></div>
  
  <p>в корневую директорию сайта</p>
  
  <p>Откройте страницу настроек WooCommerce</p>
  
  <p><a target="_blank" rel="noopener noreferrer" href=""><img src="" alt="WooCommerce Settings page" style="max-width: 100%;"></a></p>
  
  <p>Откройте вкладку <code>Checkout</code></p>
  
  <p><a target="_blank" rel="noopener noreferrer" href=""><img src="" alt="Checkout Tab" style="max-width: 100%;"></a></p>
  
  <p>Откройте вкладку <code>OSON</code> и внесите необходимые данные.</p>
  
  <p><a target="_blank" rel="noopener noreferrer" href=""><img src="" alt="Oson Settings" style="max-width: 100%;"></a></p>
  <p>Скопируйте ваш <code>Endpoint URL</code> и внесите его в кабинете поставщика Business OSON.</p>
  <p><a target="_blank" rel="noopener noreferrer" href=""><img src="" alt="Set Endpoint URL" style="max-width: 100%;"></a></p>
  


Модуль позволяет организовать оплату товаров в магазине через Oson.uz


