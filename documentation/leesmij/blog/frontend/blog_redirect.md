# Blog redirect

De homepage toont de bloglijst, dus `/blog` stuurt door naar `/`; twee URL's met dezelfde inhoud verwarren alleen.

Het is bewust een tijdelijke redirect (302), geen permanente (301). Zo kan `/blog` later nog een eigen invulling
krijgen, bijvoorbeeld een archief, zonder dat zoekmachines de URL al hebben afgeschreven.
