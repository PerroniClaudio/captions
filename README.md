Questo script è una tech demo per la generazione di sottotitoli automatici tramite Google Cloud Text-to-Speech API. È stato creato per labor medical, ma voglio renderlo buono e fruibile in modo da poterlo riutilizzare in futuro.

Utilizza la piattaforma Google Cloud Platform per svolgere i seguenti step:
-  Estrazione dell'audio dal video usando ffmpeg
-  Upload dell'audio al bucket di Google Cloud Storage
-  Upload del video al bucket di Google Cloud Storage
-  Estrazione del testo dall'audio usando Google Cloud Speech-to-Text API
-  Traduzione del testo estratto in altre lingue usando Google Cloud Translation API
-  Upload dei file .vtt generati al bucket di Google Cloud Storage

Per utilizzare questo script, è necessario avere un account Google Cloud Platform e abilitare le API necessarie, ovvero:

-  Cloud Speech-to-Text API
-  Cloud Storage API
-  Cloud Translation API

Per abilitarle è necessario creare un progetto e poi utilizzare l'utility gcloud per abilitare le API necessarie con i seguenti comandi:
```sh
gcloud  services  enable  speech.googleapis.com
gcloud  services  enable  storage.googleapis.com
gcloud  services  enable  translate.googleapis.com
```