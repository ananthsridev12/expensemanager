import 'package:flutter/material.dart';

final ThemeData appTheme = ThemeData(
  useMaterial3: true,
  colorSchemeSeed: Colors.teal,
  brightness: Brightness.light,
  inputDecorationTheme: const InputDecorationTheme(
    border: OutlineInputBorder(),
    filled: true,
    isDense: true,
  ),
  cardTheme: const CardThemeData(
    elevation: 1,
    margin: EdgeInsets.symmetric(horizontal: 16, vertical: 6),
  ),
  appBarTheme: const AppBarTheme(
    centerTitle: true,
  ),
);
