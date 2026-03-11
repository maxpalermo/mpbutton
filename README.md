# MP Button Module

## Scopo del Modulo

**MP Button** è un modulo PrestaShop avanzato per la gestione e visualizzazione di contenuti dinamici (banner, popup, widget) in diverse posizioni dello shop. Permette di mostrare contenuti HTML personalizzati con effetti fade, gestione temporale e targeting avanzato basato su categorie, prodotti, caratteristiche e controller.

## Caratteristiche Principali

### 🎯 Posizionamento Flessibile

Il modulo supporta 7 diverse posizioni per i contenuti:

- **Top** - Parte superiore della pagina (centrato orizzontalmente)
- **Bottom** - Parte inferiore della pagina (centrato orizzontalmente, con supporto per widget esterni come WhatsApp)
- **Left** - Lato sinistro (centrato verticalmente)
- **Right** - Lato destro (centrato verticalmente)
- **Center** - Centro pagina (popup modale)
- **After Cart** - Dopo l'aggiunta al carrello
- **Description** - All'interno della descrizione prodotto

### ⏱️ Gestione Temporale

- **Delay**: Ritardo prima della visualizzazione (in secondi)
- **Expire**: Durata della visualizzazione prima della scomparsa automatica (in secondi)
- **Date Start/End**: Periodo di validità del contenuto

### 🎨 Effetti Visivi

- Animazioni fade-in/fade-out fluide (500ms)
- Impilamento automatico di elementi multipli
- Gestione z-index dinamica
- Background trasparente di default

### 🎯 Targeting Avanzato

I contenuti possono essere mostrati in base a:

- **Controller**: Pagine specifiche (home, category, product, etc.)
- **Categorie**: Categorie specifiche o categoria di default
- **Prodotti**: Prodotti specifici
- **Caratteristiche**: Feature values del prodotto
- **Attributi**: Attributi del prodotto
- **Fornitori**: Supplier specifici
- **Produttori**: Manufacturer specifici
- **Gruppi clienti**: Gruppi utente specifici

## Utilizzo

### 1. Installazione

1. Carica il modulo nella cartella `/modules/mpbutton`
2. Installa il modulo dal backoffice PrestaShop
3. Il modulo creerà automaticamente le tabelle necessarie:
   - `ps_mp_button`
   - `ps_mp_button_lang`

### 2. Configurazione Backend

#### Creazione di un Nuovo Button

1. Vai su **Moduli** → **MP Button** → **Aggiungi nuovo**
2. Compila i campi:

**Tab Generale:**

- **Titolo**: Nome identificativo del button
- **Attivo**: Abilita/disabilita il button
- **Posizione**: Ordine di visualizzazione (0 = primo)
- **Layer**: Posizione sullo schermo (0-6)
  - 0 = Center (popup)
  - 1 = Top
  - 2 = Left
  - 3 = Right
  - 4 = Bottom
  - 98 = After Cart
  - 99 = Description
- **Delay**: Secondi prima della visualizzazione
- **Expire**: Secondi prima della scomparsa automatica
- **Data inizio/fine**: Periodo di validità

**Tab Contenuto:**

- **Content**: Editor HTML per il contenuto del button
- **Codeblock**: Codice HTML/JS personalizzato (opzionale)

**Tab Targeting:**

- **Gruppi clienti**: Seleziona i gruppi che vedranno il button
- **Controller**: Pagine dove mostrare il button
- **Categorie**: Categorie specifiche
- **Prodotti**: Prodotti specifici
- **Produttori/Fornitori**: Filtri aggiuntivi
- **Features/Attributes**: Targeting basato su caratteristiche prodotto

### 3. Esempi di Utilizzo

#### Esempio 1: Banner Promozionale in Bottom

```
Titolo: Black Friday Banner
Layer: 4 (Bottom)
Delay: 2 (appare dopo 2 secondi)
Expire: 0 (rimane visibile)
Content: <div style="background: #000; color: #fff; padding: 20px; text-align: center;">
           <h2>BLACK FRIDAY -50%!</h2>
         </div>
```

#### Esempio 2: Popup Centro Pagina

```
Titolo: Newsletter Popup
Layer: 0 (Center)
Delay: 5 (appare dopo 5 secondi)
Expire: 0 (rimane fino alla chiusura)
Content: <div style="background: white; padding: 30px; border-radius: 10px;">
           <h3>Iscriviti alla Newsletter</h3>
           <form>...</form>
         </div>
```

#### Esempio 3: Widget Laterale per Categoria Specifica

```
Titolo: Promo Elettronica
Layer: 3 (Right)
Categorie: [Elettronica]
Content: <div>Offerte speciali elettronica!</div>
```

#### Esempio 4: Contenuto nella Descrizione Prodotto

```
Titolo: Video Tutorial
Layer: 99 (Description)
Controller: product
Content: <iframe src="..."></iframe>
```

## Architettura Tecnica

### Frontend

**JavaScript:**

- `sticky-manager.js`: Classe unificata per gestire tutte le posizioni
- `front.js`: Funzioni helper per fade-in/fade-out

**CSS:**

- `fade.css`: Animazioni CSS per effetti fade

**Template:**

- `show_button.tpl`: Template Smarty per il rendering dei button

### Backend

**PHP:**

- `ModelMpButton.php`: Model per la gestione dei button
- `AdminMpButtonController.php`: Controller admin per CRUD operations
- `mpbutton.php`: File principale del modulo con hooks

### Database

**Tabella `ps_mp_button`:**

- Campi di configurazione (position, layer, delay, expire, date_start, date_end, active)
- Campi di targeting (controller_name, id_category, id_product, id_manufacturer, id_supplier, id_feature_value, id_attribute, group)

**Tabella `ps_mp_button_lang`:**

- Campi multilingua (title, content, codeblock)

## Hooks Utilizzati

- `hookDisplayHeader`: Carica CSS e JS nel frontend
- `hookActionFrontControllerSetMedia`: Registra asset frontend
- `hookDisplayTop`: Mostra button in posizione top
- `hookDisplayFooter`: Mostra button in altre posizioni
- `hookDisplayAfterAddToCart`: Mostra button dopo aggiunta al carrello

## API JavaScript

### StickyManager Class

```javascript
// Crea un manager per una posizione specifica
const manager = new StickyManager(StickyManager.POSITIONS.BOTTOM);

// Aggiungi un elemento
const element = document.createElement("div");
element.innerHTML = "<p>Contenuto</p>";
element.setAttribute("data-delay", "1000"); // 1 secondo
element.setAttribute("data-expire", "5000"); // 5 secondi
manager.addElement(element, options);

// Rimuovi un elemento
manager.removeElement(element);
```

### Posizioni Disponibili

```javascript
StickyManager.POSITIONS = {
    BOTTOM: "bottom",
    TOP: "top",
    LEFT: "left",
    RIGHT: "right",
    CENTER: "center",
    AFTER_CART: "after_cart",
    DESCRIPTION: "description",
};
```

## Web Component: `<mp-json-tree>`

Questo modulo include un web-component **vanilla JS** (Shadow DOM, stile isolato) per visualizzare e selezionare valori da un albero JSON.

### File

- `views/assets/js/Tree.js`

### Caratteristiche

- **Grafica moderna** e indipendente dal resto della pagina (Shadow DOM)
- **Barra di ricerca** live
- **Toolbar**
  - espandi/comprimi albero
  - seleziona tutto / togli selezione (solo in modalità multi)
- **Selezione**
  - `single` (solo 1 valore)
  - `multi` (più valori)

### Utilizzo base

```html
<script src="/modules/mpbutton/views/assets/js/Tree.js"></script>

<mp-json-tree id="featuresTree" selection-mode="multi"></mp-json-tree>

<script>
  const tree = document.getElementById('featuresTree');
  tree.setData({
    "Colori": {
      "Rosso": {"value": 10, "selected": 1},
      "Blu": {"value": 11, "selected": 0}
    }
  });

  tree.addEventListener('selection-change', (e) => {
    console.log('selected values', e.detail.values);
  });
</script>
```

### Caricamento JSON da URL

```html
<mp-json-tree src="/path/to/tree.json" selection-mode="single"></mp-json-tree>
```

### Attributi

- `src`
  - URL di un file JSON da caricare (fetch)
- `selection-mode`
  - `single` oppure `multi`
- `disabled`
  - disabilita interazioni e toolbar

### Eventi

- `selection-change`
  - `detail.values`: array dei valori selezionati
  - `detail.items`: array oggetti `{ id, path, label, value }`

### Metodi pubblici

- `load(urlOrObject)`
  - se stringa: fa fetch JSON
  - se oggetto: lo imposta come data
- `setData(object)`
- `addNode({ path, label, value, selected })`
  - `path`: stringa tipo `"Cartella/Subcartella"` oppure array
  - `label`: testo del nodo foglia
  - `value`: valore associato al nodo foglia
  - `selected`: boolean
- `getSelectedValues()`
- `getSelectedItems()`
- `selectAll()` (solo multi)
- `clearSelection()`
- `expandAll()`
- `collapseAll()`

### Struttura del JSON (con più sottoalberi)

Il JSON deve essere un oggetto annidato. I **nodi foglia** sono oggetti con almeno la chiave `value`.

Esempio completo con più livelli:

```json
{
  "Catalogo": {
    "Abbigliamento": {
      "Uomo": {
        "Giacche": {
          "Giacche invernali": { "value": 101, "selected": 0 },
          "Giacche leggere": { "value": 102, "selected": 1 }
        },
        "Pantaloni": {
          "Jeans": { "value": 103, "selected": 0 },
          "Chino": { "value": 104, "selected": 0 }
        }
      },
      "Donna": {
        "Maglieria": {
          "Pullover": { "value": 201, "selected": 0 },
          "Cardigan": { "value": 202, "selected": 0 }
        }
      }
    },
    "Accessori": {
      "Cappelli": {
        "Berretti": { "value": 301, "selected": 0 },
        "Cappellini": { "value": 302, "selected": 0 }
      }
    }
  }
}
```

## Requisiti

- PrestaShop 1.6.x o superiore
- PHP 7.1 o superiore
- MySQL 5.6 o superiore

## Licenza

Academic Free License version 3.0 (AFL-3.0)

## Autore

**Massimiliano Palermo**

- Email: maxx.palermo@gmail.com
- Copyright: Since 2016 Massimiliano Palermo

## Supporto

Per assistenza o segnalazione bug, contattare l'autore via email.
