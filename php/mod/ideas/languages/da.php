<?php
/**
 * Elgg Ideas Plugin
 * @package ideas
 */

$danish = array(
	
	// Menu items and titles	
	'ideas' => "Annoncer",
	'ideas:posts' => "Annonce",
	'ideas:title' => "Markedet",
	'ideas:user:title' => "%s's Marked",
	'ideas:user' => "%s's annoncer",
	'ideas:user:friends' => "%s's venners Marked",
	'ideas:user:friends:title' => "%s's venners Marked",
	'ideas:mine' => "Mine annoncer",
	'ideas:mine:title' => "Mine annoncer p&aring; Markedet",
	'ideas:posttitle' => "%s's annonce: %s",
	'ideas:friends' => "Venners Marked",
	'ideas:friends:title' => "Mine venners annoncer p&aring; Markedet",
	'ideas:everyone:title' => "Alt på Markedet",
	'ideas:everyone' => "Hele Markedet",
	'ideas:read' => "Vis annonce",
	'ideas:add' => "Opret ny annonce",
	'ideas:add:title' => "Opret en ny annonce p&aring; Markedet",
	'ideas:edit' => "Ret annonce",
	'ideas:imagelimitation' => "(Skal være JPG, GIF eller PNG)",
	'ideas:text' => "Giv en kort beskrivelse af tingen",
	'ideas:uploadimages' => "Tilføj billeder til din annonce.",
	'ideas:uploadimage1' => "Billede 1 (forside billede)",
	'ideas:uploadimage2' => "Billede 2",
	'ideas:uploadimage3' => "Billede 3",
	'ideas:uploadimage4' => "Billede 4",
	'ideas:image' => "Annonce billede",
	'ideas:delete:image' => "Slet dette billede",
	'ideas:imagelater' => "",
	'ideas:strapline' => "Oprettet",
	'item:object:ideas' => 'Markeds annoncer',
	'ideas:none:found' => 'Ingen annoncer fundet',
	'ideas:pmbuttontext' => "Send privat besked",
	'ideas:video' => "Video",
	'ideas:video:help' => "(i %s)",
	'ideas:text:help' => "(Ingen HTML og maks. %s tegn)",
	'ideas:title:help' => "(1-3 ord)",
	'ideas:tags' => "Nøgleord",
	'ideas:tags:help' => "(Adskil med kommaer)",
	'ideas:access:help' => "(Hvem kan se denne annonce)",
	'ideas:replies' => "Svar",
	'ideas:created:gallery' => "Oprettet af %s <br>D. %s",
	'ideas:created:listing' => "Oprettet af %s D. %s",
	'ideas:showbig' => "Vis større billedee",
	'ideas:type' => "Type",
	'ideas:type:choose' => 'Vælg annoncetype',
	'ideas:choose' => "Vælg en...",
	'ideas:charleft' => "tegn tilbage",
	'ideas:accept:terms' => "Jeg har læst og forstået %s",
	'ideas:terms' => "betingelser",
	'ideas:terms:title' => "Betingelser for brug",
	'ideas:terms' => "<li class='elgg-divide-bottom'>Annoncerne er et brugtmarked for medlemmerne.</li>
			<li class='elgg-divide-bottom'>Vi tillader kun en annonce pr. ting.</li>
			<li class='elgg-divide-bottom'>En annonce må kun indeholde een ting, medmindre de hører sammen som et sæt.</li>
			<li class='elgg-divide-bottom'>Der må kun annonceres for brugte/hjemmelavede ting.</li>
			<li class='elgg-divide-bottom'>Når du har opnået det ønskede med annoncen skal den slettes.</li>
			<li class='elgg-divide-bottom'>Annoncer slettes automatisk efter %s måned(er).</li>
			<li class='elgg-divide-bottom'>Erhvervsmæssig annoncering er kun for dem der har tegnet en reklameaftale med os.</li>
			<li class='elgg-divide-bottom'>Vi forbeholder os retten til at slette annoncer vi mener overtræder, eller forsøger at omgå, betingelserne for brug.</li>
			<li class='elgg-divide-bottom'>Betingelserne kan til enhver tid ændres.</li>
			",
	'ideas:new:post' => "Ny annonce i Markedet",
	'ideas:notification' =>
'%s tilføjede en ny annonce i Markedet:

%s - %s
%s

Se annoncen her:
%s
',
	
	// ideas widget
	'ideas:widget' => "Mit Marked",
	'ideas:widget:description' => "Fremvis Marked",
	'ideas:widget:viewall' => "Vis alt på mit Marked",
	'ideas:num_display' => "Antal der skal vises",
	'ideas:icon_size' => "Ikon størrelse",
	'ideas:small' => "lille",
	'ideas:tiny' => "mikro",
		
	// ideas river
	'river:create:object:ideas' => '%s oprettede en annonce på Det Marked med titlen %s',
	'river:update:object:ideas' => '%s opdaterede annoncen %s i Markedet',
	'river:comment:object:ideas' => '%s skrev et svar til annoncen %s',

	// Status messages
	'ideas:posted' => "Din annonce blev oprettet.",
	'ideas:deleted' => "Din annonce er blevet slettet.",
	'ideas:uploaded' => "Dit billede blev tilføjet.",
	'ideas:image:deleted' => "Dit billede blev slettet.",

	// Error messages	
	'ideas:save:failure' => "Din annonce kunne ikke oprettes. Pr&oslash;v igen.",
	'ideas:error:missing:title' => "Fejl: Du skal angive en titel!",
	'ideas:error:missing:description' => "Fejl: Du skal skrive en beskrivelse!",
	'ideas:error:missing:ideascategory' => "Fejl: Du skal angive en kategori!",
	'ideas:error:missing:video' => "Fejl: Du skal angive en video!",
	'ideas:error:missing:ideas_type' => "Fejl: Du skal angive en annoncetype!",
	'ideas:tobig' => "Fejl: Dit billede er for stort.",
	'ideas:notjpg' => "Du kan kun uploade jpg, png eller gif billeder.",
	'ideas:notuploaded' => "Fejl: Dit billede blev ikke tilføjet.",
	'ideas:image:notdeleted' => "Fejl: Dit billede blev ikke slettet!",
	'ideas:notfound' => "Fejl: Den valgte annonce kunne ikke findes.",
	'ideas:notdeleted' => "Fejl: Kunne ikke slette denne annonce.",
	'ideas:tomany' => "Fejl: For mange annoncer",
	'ideas:tomany:text' => "Du kan ikke oprette flere annoncer, slet nogen for at oprette nye!",
	'ideas:accept:terms:error' => "Du skal acceptere vores betingelser for brug!",
	'ideas:error' => "Fejl: Kan ikke gemme annonce!",
	'ideas:error:cannot_write_to_container' => "Fejl: Kan ikke skrive til container!",

	// Settings
	'ideas:settings:status' => "Status",
	'ideas:settings:desc' => "Beskrivelse",
	'ideas:max:posts' => "Maks. antal annoncer pr. bruger",
	'ideas:unlimited' => "Ubegrænset",
	'ideas:currency' => "Valuta ($, €, DKK eller noget)",
	'ideas:allowhtml' => "Tillad HTML i annoncer",
	'ideas:numchars' => "Maks antal tegn i annonce (kun tekst)",
	'ideas:pmbutton' => "Aktiver privat besked-knap",
	'ideas:adminonly' => "Kun admin kan oprette annoncer",
	'ideas:comments' => "Tillad kommentarer",
	'ideas:custom' => "Tilpasset felt",
	'ideas:settings:type' => 'Aktiver annoncetyper (køb/salg/bytte/bortgives)',	
	'ideas:type:all' => "Alle",
	'ideas:type:buy' => "Købes",
	'ideas:type:sell' => "Sælges",
	'ideas:type:swap' => "Byttes",
	'ideas:type:free' => "Bortgives",
	'ideas:expire' => "Auto slet annoncer ældre end",
	'ideas:expire:month' => "måned",
	'ideas:expire:months' => "måneder",
	'ideas:expire:subject' => "Din annonce er udløbet",
	'ideas:expire:body' => "Hej %s

Din annonce '%s', du oprettede %s, er blevet slettet.

Dette sker automatisk når annoncer er ældre end %s måned(er).",

	// ideas categories
	'ideas:categories' => 'Markeds Kategorier',
	'ideas:categories:choose' => 'Vælg kategori',
	'ideas:categories:settings' => 'Markeds Kategorier:',	
	'ideas:categories:explanation' => 'Opret nogen emnekategorier.<br>Skriv dem i formen ""clothes, footwear, furniture osv...", adskil med kommaer - husk at tilføje oversættelser i sprog-filerne som ideas:category:<i>categoryname</i>',	
	'ideas:categories:save:success' => 'Annonce kategorier blev gemt.',
	'ideas:categories:settings:categories' => 'Markeds Kategorier',
	'ideas:category:all' => "Alle",
	'ideas:category' => "Kategori",
	'ideas:category:title' => "Kategori: %s",

	// Categories
	'ideas:category:clothes' => "Tøj/sko",
	'ideas:category:furniture' => "Møbler og indretning",
	
	// Custom select
	'ideas:custom:select' => "Angiv tingens tilstand",
	'ideas:custom:text' => "Stand",
	'ideas:custom:activate' => "Aktiver tilpasset vælger:",
	'ideas:custom:settings' => "Valgmuligheder for tilpasset vælger",
	'ideas:custom:choices' => "Opret nogen valg til den tilpassede dropdown boks.<br>Valgmulighederne kunne fx. v&aelig;re \"ideas:custom:new,ideas:custom:used...osv\", adskil med kommaer - husk at tilf&oslash;je overs&aelig;ttelser i sprog-filerne.",

	// Custom choises
	 'ideas:custom:na' => "Ikke angivet",
	 'ideas:custom:new' => "Ny",
	 'ideas:custom:unused' => "Ubrugt",
	 'ideas:custom:used' => "Brugt",
	 'ideas:custom:good' => "God",
	 'ideas:custom:fair' => "Rimelig",
	 'ideas:custom:poor' => "Dårlig",
);
					
add_translation("da",$danish);

