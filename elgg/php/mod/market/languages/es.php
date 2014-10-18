<?php
/**
 * Elgg Market Plugin
 * @package market
 */

/*
 * Elgg Market Plugin
 * Spanish language file
 * Xavier Paez
 * Xinergie Systems
 * xepaez@xinergie.com.ar
 * 2014
*/

$spanish = array(
	
	// Menu items and titles	
	'market' => "Anuncio",
	'market:posts' => "Anuncios",
	'market:title' => "Tienda en linea",
	'market:user:title' => "%s's anuncios en la Tienda",
	'market:user' => "Tienda de %s",
	'market:user:friends' => "Tienda de los amigos de %s",
	'market:user:friends:title' => "anuncios de los amigos de %s en la Tienda",
	'market:mine' => "Mi Tienda",
	'market:mine:title' => "Mis anuncios en la Tienda",
	'market:posttitle' => "Tienda de %s, item: %s",
	'market:friends' => "Tiendas de Amigos",
	'market:friends:title' => "Anuncios de mis amigos en la Tienda",
	'market:everyone:title' => "Todo en la Tienda",
	'market:everyone' => "Todos los anuncios de la Tienda",
	'market:read' => "Ver Anuncio",
	'market:add' => "Crear nuevo anuncio",
	'market:add:title' => "Crear un nuevo anuncio en la Tienda",
	'market:edit' => "Editar Anuncio",
	'market:imagelimitation' => "Debe ser JPG, GIF o PNG.",
	'market:text' => "Provea una breve descripci&oacute;n del producto",
	'market:uploadimages' => "Quisiera subir una imagen para su producto?",
	'market:uploadimage1' => "Imagen 1 (imagen de portada)",
	'market:uploadimage2' => "Imagen 2",
	'market:uploadimage3' => "Imagen 3",
	'market:uploadimage4' => "Imagen 4",
	'market:image' => "Imagen del producto",
	'market:delete:image' => "Borrar esta imagen",
	'market:imagelater' => "",
	'market:strapline' => "Creada",
	'item:object:market' => 'Anuncios de la Tienda',
	'market:none:found' => 'Ningun anuncio encontrado',
	'market:pmbuttontext' => "Enviar Mensaje Privado",
	'market:price' => "Precio",
	'market:price:help' => "(en %s)",
	'market:text:help' => "(No HTML y 250 caracteres max.)",
	'market:title:help' => "(1-3 palabras)",
	'market:tags' => "Eitquetas",
	'market:tags:help' => "(Separado por comas)",
	'market:access:help' => "(Quien puede ver este anuncio)",
	'market:replies' => "Respuestas",
	'market:created:gallery' => "Creado por %s <br>en %s",
	'market:created:listing' => "Creado por %s en %s",
	'market:showbig' => "Mostrar imagen mas grande",
	'market:type' => "Tipo",
	'market:type:choose' => 'Elegir tipo de anuncio',
	'market:choose' => "Elegir uno...",
	'market:charleft' => "caracteres restantes",
	'market:accept:terms' => "He leido y aceptado los %s de uso.",
	'market:terms' => "t&eacute;rminos",
	'market:terms:title' => "T&eacute;rminos de uso",
	'market:terms' => "<li class='elgg-divide-bottom'>La Tienda es para compra y venta de productos usados entre miembros.</li>
			<li class='elgg-divide-bottom'>No mas de %s anuncios estan permitidos por usuario.</li>

			<li class='elgg-divide-bottom'>Un anuncio debe contener solo un objeto a menos que pertenezca a un paquete.</li>
			<li class='elgg-divide-bottom'>La Tienda es solo para objetos usados y/o hechos en casa.</li>
			<li class='elgg-divide-bottom'>El anuncio debe ser borrado cuando ya no sea relevante.</li>
			<li class='elgg-divide-bottom'>Los anuncios seran borrados luego de %s month(s).</li>
			<li class='elgg-divide-bottom'>Anuncios comerciales se limitan &uacute;nicamente a aquellos que han firmado un acuerdo comercial con nosotros.</li>
			<li class='elgg-divide-bottom'>Nos reservamos el derecho de borrar cualquier anuncio que viole los t&eacute;rminos de uso.</li>
			<li class='elgg-divide-bottom'>Los t&eacute;rminos esta sujectos a cambiar con el tiempo.</li>
			",
	'market:new:post' => "Nuevo anuncio",
	'market:notification' =>
'%s creo un nuevo anuncio en la Tienda:

%s - %s
%s

Ver el anuncio aqui:
%s
',
	// market widget
	'market:widget' => "Mi Tienda",
	'market:widget:description' => "Muestra tus anuncios en la Tienda",
	'market:widget:viewall' => "ver todos los anuncios de la Tienda",
	'market:num_display' => "N&uacute;mero de anuncios a mostrar",
	'market:icon_size' => "Tama&ntilde;o de iconos",
	'market:small' => "peque&ntilde;o",
	'market:tiny' => "diminuto",
		
	// market river
	'river:create:object:market' => '%s publico un nuevo anuncio en la Tienda %s',
	'river:update:object:market' => '%s actualiz&oacute; el anuncio %s en la Tienda',
	'river:comment:object:market' => '%s coment&oacute; en la Tienda en el anuncio %s',

	// Status messages
	'market:posted' => "Tu anuncio fue publicado exitosamente.",
	'market:deleted' => "Tu anuncio fue borrado exitosamente.",
	'market:uploaded' => "Tu imagen fue subida exitosamente.",
	'market:image:deleted' => "Su imagen fue borrada exitosamente.",

	// Error messages	
	'market:save:failure' => "Tu anuncio no pudo guardarse. Trata otra vez por favor.",
	'market:error:missing:title' => "Error: Falta T&iacute;tulo!",
	'market:error:missing:description' => "Error: Falta descripci&oacute;n!",
	'market:error:missing:category' => "Error: Falta categoria!",
	'market:error:missing:price' => "Error: Falta precio!",
	'market:error:missing:market_type' => "Error: Falta tipo!",
	'market:tobig' => "Lo sentimos; la imagen supera 1MB, por favor sube un archivo m&aacute;s peque&ntilde;o.",
	'market:notjpg' => "Asegurate que la imagen es un archivo .jpg, .png or .gif.",
	'market:notuploaded' => "Lo sentimos; al parecer tu archivo no se subio.",
	'market:notfound' => "Lo sentimos; no pudimos encontrar el anuncio especifico.",
	'market:notdeleted' => "Lo sentimos; no pudimos borrar el anuncio.",
	'market:image:notdeleted' => "Lo sentimos; no pudimos borrar la imagen.!",
	'market:tomany' => "Error: Demasiados anuncios",
	'market:tomany:text' => "Has alcanzado el m&aacute;ximo n&uacute;mero de anuncio por usuario. Por favor elimina alguno ya existente para continuar!",
	'market:accept:terms:error' => "Debe aceptar los t&eacute;rminos de uso!",
	'market:error' => "Error: No se pudo guardar el anuncio!",
	'market:error:cannot_write_to_container' => "Error: NO se pudo grabar en el contenedor!",

	// Settings
	'market:settings:status' => "Estado",
	'market:settings:desc' => "Descripci&oacute;n",
	'market:max:posts' => "N&uacute;mero max. de anuncios por usuario",
	'market:unlimited' => "Ilimitado",
	'market:currency' => "Moneda ($, €, DKK u otro)",
	'market:allowhtml' => "Permitir HTML en los anuncios",
	'market:numchars' => "N&uacute;mero max. de caracteres en unn anuncio (solo valido sin HTML)",
	'market:pmbutton' => "Permitir mensajes privados",
	'market:adminonly' => "Solo el administrador puede crear anuncios",
	'market:comments' => "Permitir comentarios",
	'market:custom' => "Campo personalizado",
	'market:settings:type' => 'Permitir los tipos de anuncios (compra/venta/cambio/gratis)',	
	'market:type:all' => "Todos",
	'market:type:buy' => "Comprar",
	'market:type:sell' => "Vender",
	'market:type:swap' => "Cambio",
	'market:type:free' => "Gratis",
	'market:expire' => "Borrar automaticamente los anuncios mayores a",
	'market:expire:month' => "mes",
	'market:expire:months' => "meses",
	'market:expire:subject' => "Su anuncio ha expirado",
	'market:expire:body' => "Hola %s

Su anuncio en la tienda '%s' creado %s, ha sido borrado.

Esto sucede automaticamente cuando el anuncio supera el/los %s mes/meses.",

	// market categories
	'market:categories' => 'Categorias de Tiendas',
	'market:categories:choose' => 'Elegir tipo',
	'market:categories:settings' => 'Categorias de Tiendas:',	
	'market:categories:explanation' => 'Establecer categorias predefinidas para los anuncios.<br>Las categorias pueden ser "ropa, zapatos o vender, comprar, etc...", separar cada categoria por comas - recuerde no usar caracteres especiales y ponerlas en market:<i>nombrecategoria</i> en el archivo de idioma',
	'market:categories:save:success' => 'Categorias de Tienda exitosamente grabadas.',
	'market:categories:settings:categories' => 'Categorias de Tienda',
	'market:all' => "Todas",
	'market:category' => "Categoria",
	'market:category:title' => "Categoria: %s",

	// Categories
	'market:category:ropa' => "Ropa/zapatos",
	'market:category:muebles' => "Muebles",
	'market:category:electro' => "Electrodomesticos",

	// Custom select
	'market:custom:select' => "Condici&oacute;n del Producto",
	'market:custom:text' => "Condici&oacute;n",
	'market:custom:activate' => "Permitir Selecci&oacute;n personalizada:",
	'market:custom:settings' => "Opciones de Selecci&oacute;n personalizada",
	'market:custom:choices' => "Cargue algunas opciones de selecci&oacute;n personalizada predefinidas para la lista de despliegue.<br>Las opciones pueden ser \"market:nuevo,market:usado...etc\", sepera cada opci&oacute;n con coma - recuerde ponerlas en el archivo de idioma",

	// Custom choises
	 'market:na' => "Sin informaci&oacute;n",
	 'market:nuevo' => "Nuevo",
	 'market:nouso' => "Sin Uso",
	 'market:usado' => "Usado",
	 'market:bueno' => "Bueno",
	 'market:regular' => "Regular",
	 'market:malo' => "Malo",
);
					
add_translation('es', $spanish);

