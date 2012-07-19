<?php
namespace AllPlayers\Objects;

use stdClass;

/**
 * Define the colors you can use for groups.
 */
define("APCI_GROUP_COLOR_BLACK", "Black");
define("APCI_GROUP_COLOR_NAVY", "Navy");
define("APCI_GROUP_COLOR_DARKBLUE", "DarkBlue");
define("APCI_GROUP_COLOR_MEDIUMBLUE", "MediumBlue");
define("APCI_GROUP_COLOR_BLUE", "Blue");
define("APCI_GROUP_COLOR_DARKGREEN", "DarkGreen");
define("APCI_GROUP_COLOR_GREEN", "Green");
define("APCI_GROUP_COLOR_TEAL", "Teal");
define("APCI_GROUP_COLOR_DARKCYAN", "DarkCyan");
define("APCI_GROUP_COLOR_DEEPSKYBLUE", "DeepSkyBlue");
define("APCI_GROUP_COLOR_DARKTURQUOISE", "DarkTurquoise");
define("APCI_GROUP_COLOR_MEDIUMSPRINGGREEN", "MediumSpringGreen");
define("APCI_GROUP_COLOR_LIME", "Lime");
define("APCI_GROUP_COLOR_SPRINGGREEN", "SpringGreen");
define("APCI_GROUP_COLOR_CYAN", "Cyan");
define("APCI_GROUP_COLOR_MIDNIGHTBLUE", "MidnightBlue");
define("APCI_GROUP_COLOR_DODGERBLUE", "DodgerBlue");
define("APCI_GROUP_COLOR_LIGHTSEAGREEN", "LightSeaGreen");
define("APCI_GROUP_COLOR_FORESTGREEN", "ForestGreen");
define("APCI_GROUP_COLOR_SEAGREEN", "SeaGreen");
define("APCI_GROUP_COLOR_DARKSLATEGRAY", "DarkSlateGray");
define("APCI_GROUP_COLOR_LIMEGREEN", "LimeGreen");
define("APCI_GROUP_COLOR_MEDIUMSEAGREEN", "MediumSeaGreen");
define("APCI_GROUP_COLOR_TURQUOISE", "Turquoise");
define("APCI_GROUP_COLOR_ROYALBLUE", "RoyalBlue");
define("APCI_GROUP_COLOR_STEELBLUE", "SteelBlue");
define("APCI_GROUP_COLOR_DARKSLATEBLUE", "DarkSlateBlue");
define("APCI_GROUP_COLOR_MEDIUMTURQUOISE", "MediumTurquoise");
define("APCI_GROUP_COLOR_INDIGO", "Indigo");
define("APCI_GROUP_COLOR_DARKOLIVEGREEN", "DarkOliveGreen");
define("APCI_GROUP_COLOR_CADETBLUE", "CadetBlue");
define("APCI_GROUP_COLOR_CORNFLOWERBLUE", "CornflowerBlue");
define("APCI_GROUP_COLOR_MEDIUMAQUAMARINE", "MediumAquaMarine");
define("APCI_GROUP_COLOR_DIMGRAY", "DimGray");
define("APCI_GROUP_COLOR_SLATEBLUE", "SlateBlue");
define("APCI_GROUP_COLOR_OLIVEDRAB", "OliveDrab");
define("APCI_GROUP_COLOR_SLATEGRAY", "SlateGray");
define("APCI_GROUP_COLOR_LIGHTSLATEGRAY", "LightSlateGray");
define("APCI_GROUP_COLOR_MEDIUMSLATEBLUE", "MediumSlateBlue");
define("APCI_GROUP_COLOR_LAWNGREEN", "LawnGreen");
define("APCI_GROUP_COLOR_CHARTREUSE", "Chartreuse");
define("APCI_GROUP_COLOR_AQUAMARINE", "Aquamarine");
define("APCI_GROUP_COLOR_MAROON", "Maroon");
define("APCI_GROUP_COLOR_PURPLE", "Purple");
define("APCI_GROUP_COLOR_OLIVE", "Olive");
define("APCI_GROUP_COLOR_GRAY", "Gray");
define("APCI_GROUP_COLOR_SKYBLUE", "SkyBlue");
define("APCI_GROUP_COLOR_LIGHTSKYBLUE", "LightSkyBlue");
define("APCI_GROUP_COLOR_BLUEVIOLET", "BlueViolet");
define("APCI_GROUP_COLOR_DARKRED", "DarkRed");
define("APCI_GROUP_COLOR_DARKMAGENTA", "DarkMagenta");
define("APCI_GROUP_COLOR_SADDLEBROWN", "SaddleBrown");
define("APCI_GROUP_COLOR_DARKSEAGREEN", "DarkSeaGreen");
define("APCI_GROUP_COLOR_LIGHTGREEN", "LightGreen");
define("APCI_GROUP_COLOR_MEDIUMPURPLE", "MediumPurple");
define("APCI_GROUP_COLOR_DARKVIOLET", "DarkViolet");
define("APCI_GROUP_COLOR_PALEGREEN", "PaleGreen");
define("APCI_GROUP_COLOR_DARKORCHID", "DarkOrchid");
define("APCI_GROUP_COLOR_YELLOWGREEN", "YellowGreen");
define("APCI_GROUP_COLOR_SIENNA", "Sienna");
define("APCI_GROUP_COLOR_BROWN", "Brown");
define("APCI_GROUP_COLOR_DARKGRAY", "DarkGray");
define("APCI_GROUP_COLOR_LIGHTBLUE", "LightBlue");
define("APCI_GROUP_COLOR_GREENYELLOW", "GreenYellow");
define("APCI_GROUP_COLOR_PALETURQUOISE", "PaleTurquoise");
define("APCI_GROUP_COLOR_LIGHTSTEELBLUE", "LightSteelBlue");
define("APCI_GROUP_COLOR_POWDERBLUE", "PowderBlue");
define("APCI_GROUP_COLOR_FIREBRICK", "FireBrick");
define("APCI_GROUP_COLOR_DARKGOLDENROD", "DarkGoldenRod");
define("APCI_GROUP_COLOR_MEDIUMORCHID", "MediumOrchid");
define("APCI_GROUP_COLOR_ROSYBROWN", "RosyBrown");
define("APCI_GROUP_COLOR_DARKKHAKI", "DarkKhaki");
define("APCI_GROUP_COLOR_SILVER", "Silver");
define("APCI_GROUP_COLOR_MEDIUMVIOLETRED", "MediumVioletRed");
define("APCI_GROUP_COLOR_INDIANRED", "IndianRed");
define("APCI_GROUP_COLOR_PERU", "Peru");
define("APCI_GROUP_COLOR_CHOCOLATE", "Chocolate");
define("APCI_GROUP_COLOR_TAN", "Tan");
define("APCI_GROUP_COLOR_LIGHTGREY", "LightGrey");
define("APCI_GROUP_COLOR_PALEVIOLETRED", "PaleVioletRed");
define("APCI_GROUP_COLOR_THISTLE", "Thistle");
define("APCI_GROUP_COLOR_ORCHID", "Orchid");
define("APCI_GROUP_COLOR_GOLDENROD", "GoldenRod");
define("APCI_GROUP_COLOR_CRIMSON", "Crimson");
define("APCI_GROUP_COLOR_GAINSBORO", "Gainsboro");
define("APCI_GROUP_COLOR_PLUM", "Plum");
define("APCI_GROUP_COLOR_BURLYWOOD", "BurlyWood");
define("APCI_GROUP_COLOR_LIGHTCYAN", "LightCyan");
define("APCI_GROUP_COLOR_LAVENDER", "Lavender");
define("APCI_GROUP_COLOR_DARKSALMON", "DarkSalmon");
define("APCI_GROUP_COLOR_VIOLET", "Violet");
define("APCI_GROUP_COLOR_PALEGOLDENROD", "PaleGoldenRod");
define("APCI_GROUP_COLOR_LIGHTCORAL", "LightCoral");
define("APCI_GROUP_COLOR_KHAKI", "Khaki");
define("APCI_GROUP_COLOR_ALICEBLUE", "AliceBlue");
define("APCI_GROUP_COLOR_HONEYDEW", "HoneyDew");
define("APCI_GROUP_COLOR_AZURE", "Azure");
define("APCI_GROUP_COLOR_SANDYBROWN", "SandyBrown");
define("APCI_GROUP_COLOR_WHEAT", "Wheat");
define("APCI_GROUP_COLOR_BEIGE", "Beige");
define("APCI_GROUP_COLOR_WHITESMOKE", "WhiteSmoke");
define("APCI_GROUP_COLOR_MINTCREAM", "MintCream");
define("APCI_GROUP_COLOR_GHOSTWHITE", "GhostWhite");
define("APCI_GROUP_COLOR_SALMON", "Salmon");
define("APCI_GROUP_COLOR_ANTIQUEWHITE", "AntiqueWhite");
define("APCI_GROUP_COLOR_LINEN", "Linen");
define("APCI_GROUP_COLOR_LIGHTGOLDENRODYELLOW", "LightGoldenRodYellow");
define("APCI_GROUP_COLOR_OLDLACE", "OldLace");
define("APCI_GROUP_COLOR_RED", "Red");
define("APCI_GROUP_COLOR_MAGENTA", "Magenta");
define("APCI_GROUP_COLOR_DEEPPINK", "DeepPink");
define("APCI_GROUP_COLOR_ORANGERED", "OrangeRed");
define("APCI_GROUP_COLOR_TOMATO", "Tomato");
define("APCI_GROUP_COLOR_HOTPINK", "HotPink");
define("APCI_GROUP_COLOR_CORAL", "Coral");
define("APCI_GROUP_COLOR_DARKORANGE", "Darkorange");
define("APCI_GROUP_COLOR_LIGHTSALMON", "LightSalmon");
define("APCI_GROUP_COLOR_ORANGE", "Orange");
define("APCI_GROUP_COLOR_LIGHTPINK", "LightPink");
define("APCI_GROUP_COLOR_PINK", "Pink");
define("APCI_GROUP_COLOR_GOLD", "Gold");
define("APCI_GROUP_COLOR_PEACHPUFF", "PeachPuff");
define("APCI_GROUP_COLOR_NAVAJOWHITE", "NavajoWhite");
define("APCI_GROUP_COLOR_MOCCASIN", "Moccasin");
define("APCI_GROUP_COLOR_BISQUE", "Bisque");
define("APCI_GROUP_COLOR_MISTYROSE", "MistyRose");
define("APCI_GROUP_COLOR_BLANCHEDALMOND", "BlanchedAlmond");
define("APCI_GROUP_COLOR_PAPAYAWHIP", "PapayaWhip");
define("APCI_GROUP_COLOR_LAVENDERBLUSH", "LavenderBlush");
define("APCI_GROUP_COLOR_SEASHELL", "SeaShell");
define("APCI_GROUP_COLOR_CORNSILK", "Cornsilk");
define("APCI_GROUP_COLOR_LEMONCHIFFON", "LemonChiffon");
define("APCI_GROUP_COLOR_FLORALWHITE", "FloralWhite");
define("APCI_GROUP_COLOR_SNOW", "Snow");
define("APCI_GROUP_COLOR_YELLOW", "Yellow");
define("APCI_GROUP_COLOR_LIGHTYELLOW", "LightYellow");
define("APCI_GROUP_COLOR_IVORY", "Ivory");
define("APCI_GROUP_COLOR_WHITE", "White");

/**
 * AllPlayers group.
 */
abstract class Group extends stdClass {
  /**
   * @var string
   */
  public $title;

  /**
   * @var string
   */
  public $description;

  /**
   * @var string
   */
  public $zip;

  /**
   * @var string
   */
  public $category;

  /**
   * @var string
   */
  public $primary_color;

  /**
   * @var string
   */
  public $secondary_color;
}
