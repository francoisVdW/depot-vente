<?php
/**
 * Created by FVdW.
 *
 * Trairies @ raspberry
 * User: francois
 * Date: 31/10/2020
 *
 * @copyright: 2020
 * @version $Revision: $
 */
return [
    'app_title' => 'Dépôt - Vente',
    'roles' => [
        'ADMIN'    => 'Admin.',
        'MNGR'     => 'Gestion',
        'C-REGIST' => 'Caisse (vente)',
        'DEPOSIT'  => 'Dépôt',
    ],
    'cash_register' => [
        'states' => [
            'OPEN' => [
                'label' => 'Ouverte',
                'icon' => 'fa fa-play-circle',
            ],
            'CLOSED' => [
                'label' => 'Fermée',
                'icon' => 'fa fa-stop-circle',
            ],
            'SALE IN PROGRESS' => [
                'label' => 'Vente en cours',
                'icon' => 'fa fa-cog fa-spin fa-fw',
            ],
            'COMPLETED' => [
                'label' => 'Clôturée',
                'icon' => 'fa fa-lock',
            ],
        ]
    ],
    'sales' => [
        'states' => [
            'NEW' => [
                'label' => 'Nouvelle vente',
                'icon' => '',
            ],
            'SALE IN PROGRESS' => [
                'label' => 'Vente en cours',
                'icon' => 'fa fa-play-circle',
            ],
            'DONE' => [
                'label' => 'Vente finalisée',
                'icon' => 'fa fa-check-circle text-success',
            ],
            'CANCELED' => [
                'label' => 'Abandonnée',
                'icon' => 'fa fa-stop-circle',
            ],
        ]
    ],
    'deposits' => [
        'progresses' => [
            'EDIT' => [
                'label' => 'En cours d\'édition',
                'icon' => 'fa fa-pencil-square-o',
            ],
            'CLOSED' => [
                'label' => 'Clôturé',
                'icon' => 'fa fa-lock',
            ],
        ],
    ],
    'items' => [
        'progresses' => [
            'EDIT' => [
                'label' => 'En cours d\'édition',
                'icon' => 'fa fa-pencil-square-o',
            ],
            'ON SALE' => [
                'label' => 'Disponnible à la vente',
                'icon' => 'fa fa-star-o',
            ],
            'LOCKED' => [
                'label' => 'En cours de vente',
                'icon' => 'fa fa-circle-o-notch fa-spin',
            ],
            'SOLD' => [
                'label' => 'Vendu',
                'icon' => 'fa fa-check-circle text-success',
            ],
            'RETURNED' => [
                'label' => 'Retourné et non remis en vente',
                'icon' => 'fa fa-ban text-danger'
            ],
        ],
        'available_colors' => [
            'gray' => '<i class="fa fa-circle lead" aria-hidden="true" style="color: grey;"></i> Gris',
            'black' => '<i class="fa fa-circle lead" aria-hidden="true" style="color: black;"></i> Noir',
            'yellow' => '<i class="fa fa-circle lead" aria-hidden="true" style="color: yellow;"></i> Jaune',
            'lime' => '<i class="fa fa-circle lead" aria-hidden="true" style="color: lime;"></i> Lemon',
            'green' => '<i class="fa fa-circle lead" aria-hidden="true" style="color: green;"></i> Vert',
            'olive' => '<i class="fa fa-circle lead" aria-hidden="true" style="color: olive;"></i> Olive',
            'orange' => '<i class="fa fa-circle lead" aria-hidden="true" style="color: orange;"></i> Orange',
            'red' => '<i class="fa fa-circle lead" aria-hidden="true" style="color: red;"></i> Rouge',
            'fuchsia' => '<i class="fa fa-circle lead" aria-hidden="true" style="color: fuchsia;"></i> Fuchsia',
            'aqua' => '<i class="fa fa-circle lead" aria-hidden="true" style="color: aqua;"></i> Aqua',
            'blue' => '<i class="fa fa-circle lead" aria-hidden="true" style="color: blue;"></i> Bleu',
        ],

    ],
    // Mise en page du document PDF "bon de dépot"
    'deposit_pdf_pattern' => [
        'pdf_tpl' => 'pdf_template.pdf',
        'general'=> [
            'subject'=>'Reçu dépôt',	// propriete  'subject' du fichier pdf
            'line_height' => 12,       // hauteur de ligne en px
            'font_name'=>'helvetica', 	// courier | helvetica | times | symbol
            'font_size'=>12,    	// in px
            'font_style'=>'', 		// (B|I)
            'left'=>10,      		// page : marge de gauche en mm
            'top'=>20,       		// page : marge du haut en mm
            'items_frame'=> [
                'y'=>80,
                'x'=>10,
                'h'=>16,
                'w'=>180,
                'font_size'=>11,
                'font_style'=>'',
            ],
        ],
        'head'=> [
            // exemplaire Déposant | Organisateur
            'dest' => [
                'font_size'=>18,    	// in px
                'font_style'=>'i', 		// (B|I)
                'align'=>'L',           // L|R|C
                'x'=> 10,     			// position de la cellule X en mm
                'y'=> 51,            	// position de la cellule Y en mm
                'w'=>100,
                'prefix'=>"Exemplaire ",
                'multi_line'=>false,    // cellule multi-ligne true | false
            ],

            // n° dépot
            'id' => [
                'font_size'=>25,    	// in px
                'font_style'=>'B', 		// (B|I)
                'align'=>'L',           // L|R|C
                'x'=> 110,     			// position de la cellule X en mm
                'y'=> 50,            	// position de la cellule Y en mm
                'w'=>90,
                'prefix'=>"Dépôt N° ",
                'multi_line'=>false,    // cellule multi-ligne true | false
            ],
            // Déposant
            'title' => [    // nom déposant
                'font_size'=>14,    	// in px
                'font_style'=>'B', 		// (B|I)
                'align'=>'L',           // L|R|C
                'x'=> 110,     			// position de la cellule X en mm
                'y'=> 60,            	// position de la cellule Y en mm
                'w'=>90,
                'prefix'=>"Déposant : ",
                'multi_line'=>false,    // cellule multi-ligne true | false
            ],
            'tel' => [
                'font_size'=>12,    	// in px
                'font_style'=>'', 		// (B|I)
                'align'=>'L',           // L|R|C
                'x'=> 110,     			// position de la cellule X en mm
                'y'=> 65,            	// position de la cellule Y en mm
                'w'=>90,
                'prefix'=>"Tél : ",
                'multi_line'=>false,    // cellule multi-ligne true | false
            ],
            'email' => [
                'font_size'=>10,    	// in px
                'font_style'=>'', 		// (B|I)
                'align'=>'L',           // L|R|C
                'x'=> 110,     			// position de la cellule X en mm
                'y'=> 70,            	// position de la cellule Y en mm
                'w'=>90,
                'prefix'=>"e-mail : ",
                'multi_line'=>false,    // cellule multi-ligne true | false
            ],
            // - - - - - - - - - - - - -
            // date
            'created' => [
                'font_size'=>11,    	// in px
                'font_style'=>'', 		// (B|I)
                'align'=>'L',           // L|R|C
                'x'=> 10,     			// position de la cellule X en mm
                'y'=> 62,            	// position de la cellule Y en mm
                'w'=>100,
                'multi_line'=>false,    // cellule multi-ligne true | false
                'prefix'=>'Enregistré le ',
            ],
            'creator' => [
                'font_size'=>11,    	// in px
                'font_style'=>'', 		// (B|I)
                'align'=>'L',           // L|R|C
                'x'=> 10,     			// position de la cellule X en mm
                'y'=> 67,            	// position de la cellule Y en mm
                'w'=>100,
                'multi_line'=>false,    // cellule multi-ligne true | false
                'prefix'=>'par ',
            ],
        ], // fin head
        'items'=> [ // Les items sont tjs traités en "MultiCell" : ATTENTION w est obligatoire
            // ----------------------------------------------------------
            'ref' => [
                'font_size'=>12,
                'font_style'=>'B',
                'align'=>'L',
                'w'=>20,
                'prefix'=>'',
                'border' => 'T',
            ],
            'name' => [             // nom du champ
                'font_size'=>11,    // in px
                'font_style'=>'',
                'align'=>'L',       // L|R|C
                'w'=>140,           // largeur de la cellule (champ) en mm - MINIMUN 3 mm !
                'sufix'=>'',
                'border' => 'T',
            ],
            'requested_price' => [
                'font_size'=>12,
                'font_style'=>'',
                'align'=>'R',
                'w'=>20,
                'sufix'=>'€',
                'border' => 'T',
            ],
            'happy_hour' => [
                'font_size'=>11,
                'font_style'=>'B',
                'align'=>'C',
                'w'=>10,
                'border' => 'T',
            ],
            'note' => [          // nom du champ :  note du Véto
                'font_size'=>12,    // in px
                'align'=>'L',       // L|R|C
                'font_style'=>'I',
                'w'=>180,           // largeur de la cellule (champ) en mm - MINIMUN 3 mm !
                'alone'=>true		// cette donnée est seule sur la ligne, les autres données sont IGNOREES !
            ],
        ], // fin items
        'foot'=> [],
    ],
    // Mise en page du document PDF "bon de dépot"
    'invoice_pdf_pattern' => [
        'pdf_tpl' => 'pdf_template.pdf',
        'general'=> [
            'subject'=>'Reçu dépôt',	// propriete  'subject' du fichier pdf
            'line_height' => 12,       // hauteur de ligne en px
            'font_name'=>'helvetica', 	// courier | helvetica | times | symbol
            'font_size'=>12,    	// in px
            'font_style'=>'', 		// (B|I)
            'left'=>10,      		// page : marge de gauche en mm
            'top'=>20,       		// page : marge du haut en mm
            'items_frame'=> [
                'y'=>80,
                'x'=>10,
                'h'=>16,
                'w'=>180,
                'font_size'=>11,
                'font_style'=>'',
            ],
        ],
        'head'=> [
            // n° Fact
            'invoice_num' => [
                'font_size'=>20,    	// in px
                'font_style'=>'B', 		// (B|I)
                'align'=>'L',           // L|R|C
                'x'=> 10,     			// position de la cellule X en mm
                'y'=> 45,            	// position de la cellule Y en mm
                'w'=>90,
                'prefix'=>"Facture : ",
                'multi_line'=>false,    // cellule multi-ligne true | false
            ],
            // Client : nom + adresse client
            'customer_info' => [    // nom déposant
                'font_size'=>14,    	// in px
                'font_style'=>'', 		// (B|I)
                'align'=>'L',           // L|R|C
                'x'=> 120,     			// position de la cellule X en mm
                'y'=> 45,            	// position de la cellule Y en mm
                'w'=>80,
                'prefix'=> "Facture à : \n",
                'multi_line'=>true,    // cellule multi-ligne true | false
            ],
            // - - - - - - - - - - - - -
            // date
            'created' => [
                'font_size'=>11,    	// in px
                'font_style'=>'', 		// (B|I)
                'align'=>'L',           // L|R|C
                'x'=> 10,     			// position de la cellule X en mm
                'y'=> 62,            	// position de la cellule Y en mm
                'w'=>100,
                'multi_line'=>false,    // cellule multi-ligne true | false
                'prefix'=>'Date ',
            ],
        ], // fin head
        'items'=> [ // Les items sont tjs traités en "MultiCell" : ATTENTION w est obligatoire
            // ----------------------------------------------------------
            'ref' => [
                'font_size'=>12,
                'font_style'=>'B',
                'align'=>'L',
                'w'=>20,
                'prefix'=>'',
                'border' => 'T',
            ],
            'name' => [             // nom du champ
                'font_size'=>11,    // in px
                'font_style'=>'',
                'align'=>'L',       // L|R|C
                'w'=>150,           // largeur de la cellule (champ) en mm - MINIMUN 3 mm !
                'sufix'=>'',
                'border' => 'T',
            ],
            'sale_price' => [
                'font_size'=>12,
                'font_style'=>'',
                'align'=>'R',
                'w'=>20,
                'sufix'=>'€',
                'border' => 'T',
            ],
            'ttl' => [
                'alone' => true,
                'w' => 190,
                'align' => 'R',
                'suffix' => '€',
                'prefix' => 'TOTAL : ',
                'font_size'=>12,
                'font_style'=>'B',
                'border' => 'T',
            ]
        ], // fin items
        'foot'=> [],
    ],
    // Mise en page du document PDF "balance"
    'balance_pdf_pattern' => [
        'pdf_tpl' => 'pdf_template.pdf',
        'general'=> [
            'subject'=>'Balance dépôt',	// propriete  'subject' du fichier pdf
            'line_height' => 12,       // hauteur de ligne en px
            'font_name'=>'helvetica', 	// courier | helvetica | times | symbol
            'font_size'=>12,    	// in px
            'font_style'=>'', 		// (B|I)
            'left'=>10,      		// page : marge de gauche en mm
            'top'=>20,       		// page : marge du haut en mm
            'items_frame'=> [
                'y'=>70,
                'x'=>10,
                'h'=>16,
                'w'=>180,
                'font_size'=>11,
                'font_style'=>'',
            ],
        ],
        'head'=> [
            // n° dépot
            'id' => [
                'font_size'=>25,    	// in px
                'font_style'=>'', 		// (B|I)
                'align'=>'L',           // L|R|C
                'x'=> 10,     			// position de la cellule X en mm
                'y'=> 50,            	// position de la cellule Y en mm
                'w'=>90,
                'prefix'=>"Dépôt N° ",
                'multi_line'=>false,    // cellule multi-ligne true | false
            ],
            // Déposant
            'title' => [    // nom déposant
                'font_size'=>13,    	// in px
                'font_style'=>'B', 		// (B|I)
                'align'=>'L',           // L|R|C
                'x'=> 110,     			// position de la cellule X en mm
                'y'=> 50,            	// position de la cellule Y en mm
                'w'=>90,
                'prefix'=>"Déposant : ",
                'multi_line'=>false,    // cellule multi-ligne true | false
            ],
            'tel' => [
                'font_size'=>12,    	// in px
                'font_style'=>'', 		// (B|I)
                'align'=>'L',           // L|R|C
                'x'=> 110,     			// position de la cellule X en mm
                'y'=> 55,            	// position de la cellule Y en mm
                'w'=>90,
                'prefix'=>"Tél : ",
                'multi_line'=>false,    // cellule multi-ligne true | false
            ],
            'email' => [
                'font_size'=>10,    	// in px
                'font_style'=>'', 		// (B|I)
                'align'=>'L',           // L|R|C
                'x'=> 110,     			// position de la cellule X en mm
                'y'=> 60,            	// position de la cellule Y en mm
                'w'=>90,
                'prefix'=>"e-mail : ",
                'multi_line'=>false,    // cellule multi-ligne true | false
            ],
        ], // fin head
        'items'=> [ // Les items sont tjs traités en "MultiCell" : ATTENTION w est obligatoire
            // ----------------------------------------------------------
            'status' => [
                'font_size'=>11,
                'font_style'=>'BI',
                'align'=>'L',
                'w'=>25,
                'prefix'=>'',
                'border' => 'T',
            ],
            'ref' => [
                'font_size'=>12,
                'font_style'=>'B',
                'align'=>'L',
                'w'=>20,
                'prefix'=>'',
                'border' => 'T',
            ],
            'name' => [             // nom du champ
                'font_size'=>11,    // in px
                'font_style'=>'',
                'align'=>'L',       // L|R|C
                'w'=>115,           // largeur de la cellule (champ) en mm - MINIMUN 3 mm !
                'sufix'=>'',
                'border' => 'T',
            ],
            'debt_amount' => [
                'font_size'=>12,
                'font_style'=>'',
                'align'=>'R',
                'w'=>20,
                'sufix'=>'€',
                'border' => 'T',
            ],
            'happy_hour_applied' => [
                'font_size'=>11,
                'font_style'=>'B',
                'align'=>'C',
                'w'=>10,
                'border' => 'T',
            ],
            'return' => [
                'alone' => true,
                'w' => 190,
                'align' => 'L',
                'font_size'=>12,
            ],
            'ttl' => [
                'alone' => true,
                'w' => 190,
                'font_style'=>'B',
                'align'=>'C',
                'font_size'=>14,
                'border' => true,
            ]
        ], // fin items
        'foot'=> [],
    ],
];
// EoF
